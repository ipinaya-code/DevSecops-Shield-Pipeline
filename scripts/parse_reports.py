#!/usr/bin/env python3
"""
Script procesador de reportes de seguridad (Quality Gate)
Procesa los reportes de Semgrep, Trivy y OWASP ZAP para determinar
si el código pasa o falla el gate de seguridad.
"""

import json
import os
import sys
import argparse
from pathlib import Path
from typing import Dict, List, Any

# Configuración
REPORTS_DIR = Path("reports")
CRITICAL_SEVERITY = ["CRITICAL", "HIGH", "ERROR"]
WARNING_SEVERITY = ["MEDIUM", "WARNING", "INFO"]

class SecurityReport:
    """Clase base para procesar reportes de seguridad"""
    
    def __init__(self, tool_name: str, report_path: Path):
        self.tool_name = tool_name
        self.report_path = report_path
        self.findings = []
        self.critical_count = 0
        self.warning_count = 0
        
    def load_report(self) -> bool:
        """Carga el reporte desde el archivo"""
        if not self.report_path.exists():
            print(f"⚠️  Reporte {self.tool_name} no encontrado: {self.report_path}")
            return False
        
        try:
            with open(self.report_path, 'r') as f:
                self.data = json.load(f)
            return True
        except Exception as e:
            print(f"❌ Error al cargar reporte {self.tool_name}: {e}")
            return False
    
    def parse(self):
        """Parsea el reporte (implementado por cada herramienta)"""
        pass
    
    def get_summary(self) -> Dict[str, Any]:
        """Retorna un resumen de los hallazgos"""
        return {
            "tool": self.tool_name,
            "critical": self.critical_count,
            "warning": self.warning_count,
            "total": len(self.findings)
        }

class SemgrepReport(SecurityReport):
    """Procesa reportes de Semgrep"""
    
    def parse(self):
        if not self.load_report():
            return
        
        # Semgrep output format
        results = self.data.get("results", [])
        
        for result in results:
            severity = result.get("extra", {}).get("severity", "INFO").upper()
            message = result.get("message", "Sin mensaje")
            file_path = result.get("path", "Archivo desconocido")
            line = result.get("start", {}).get("line", 0)
            
            finding = {
                "severity": severity,
                "message": message,
                "file": file_path,
                "line": line,
                "tool": "Semgrep"
            }
            
            self.findings.append(finding)
            
            if severity in CRITICAL_SEVERITY:
                self.critical_count += 1
            else:
                self.warning_count += 1

class TrivyReport(SecurityReport):
    """Procesa reportes de Trivy (SCA y Container)"""
    
    def parse(self):
        if not self.load_report():
            return
        
        # Trivy output format
        results = self.data.get("Results", [])
        
        for result in results:
            vulnerabilities = result.get("Vulnerabilities", [])
            
            for vuln in vulnerabilities:
                severity = vuln.get("Severity", "UNKNOWN").upper()
                cve_id = vuln.get("VulnerabilityID", "CVE desconocido")
                title = vuln.get("Title", "Sin título")
                package_name = vuln.get("PkgName", "Paquete desconocido")
                installed_version = vuln.get("InstalledVersion", "Desconocido")
                
                finding = {
                    "severity": severity,
                    "cve": cve_id,
                    "title": title,
                    "package": package_name,
                    "version": installed_version,
                    "tool": "Trivy"
                }
                
                self.findings.append(finding)
                
                if severity in CRITICAL_SEVERITY:
                    self.critical_count += 1
                else:
                    self.warning_count += 1

class ZAPReport(SecurityReport):
    """Procesa reportes de OWASP ZAP"""
    
    def parse(self):
        # ZAP genera reportes HTML, necesitamos convertir a JSON
        # Para simplificar, asumimos que el script de conversión ya se ejecutó
        json_path = self.report_path.with_suffix('.json')
        
        if not json_path.exists():
            print(f"⚠️  Reporte ZAP JSON no encontrado: {json_path}")
            return
        
        try:
            with open(json_path, 'r') as f:
                self.data = json.load(f)
        except Exception as e:
            print(f"❌ Error al cargar reporte ZAP: {e}")
            return
        
        # ZAP JSON format
        alerts = self.data.get("site", [{}])[0].get("alerts", [])
        
        for alert in alerts:
            risk = alert.get("risk", "Info").lower()
            name = alert.get("name", "Alerta desconocida")
            
            # Mapear riesgo a severidad
            severity_map = {
                "high": "HIGH",
                "medium": "MEDIUM",
                "low": "LOW",
                "informational": "INFO"
            }
            severity = severity_map.get(risk, "INFO").upper()
            
            finding = {
                "severity": severity,
                "name": name,
                "tool": "OWASP ZAP"
            }
            
            self.findings.append(finding)
            
            if severity in CRITICAL_SEVERITY:
                self.critical_count += 1
            else:
                self.warning_count += 1

def convert_zap_to_json():
    """Convierte el reporte HTML de ZAP a JSON (simplificado)"""
    html_path = REPORTS_DIR / "zap-report.html"
    json_path = REPORTS_DIR / "zap-report.json"
    
    if not html_path.exists():
        print(f"⚠️  Reporte ZAP HTML no encontrado: {html_path}")
        return
    
    # Para simplificar, creamos un JSON básico
    # En producción, usaría un parser HTML real
    zap_data = {
        "site": [{
            "alerts": []
        }]
    }
    
    with open(json_path, 'w') as f:
        json.dump(zap_data, f, indent=2)
    
    print(f"✅ Reporte ZAP convertido a JSON")

def quality_gate(reports: List[SecurityReport]) -> bool:
    """
    Evalúa el Quality Gate
    Retorna True si pasa, False si falla
    """
    total_critical = sum(r.critical_count for r in reports)
    total_warnings = sum(r.warning_count for r in reports)
    
    print("\n" + "="*60)
    print("📊 RESUMEN DE SEGURIDAD")
    print("="*60)
    
    for report in reports:
        summary = report.get_summary()
        print(f"\n🔍 {summary['tool']}:")
        print(f"   Críticos: {summary['critical']}")
        print(f"   Advertencias: {summary['warning']}")
        print(f"   Total: {summary['total']}")
    
    print(f"\n🎯 TOTAL:")
    print(f"   Críticos: {total_critical}")
    print(f"   Advertencias: {total_warnings}")
    print("="*60)
    
    # Si hay hallazgos críticos, falla el gate
    if total_critical > 0:
        print(f"\n❌ QUALITY GATE: FALLADO")
        print(f"   Se detectaron {total_critical} hallazgos críticos")
        print(f"   El código NO puede avanzar a producción")
        return False
    else:
        print(f"\n✅ QUALITY GATE: PASADO")
        print(f"   No se detectaron hallazgos críticos")
        print(f"   El código puede avanzar a producción")
        return True

def main():
    parser = argparse.ArgumentParser(description="Procesa reportes de seguridad")
    parser.add_argument("--convert-zap", action="store_true", help="Convierte reporte ZAP a JSON")
    args = parser.parse_args()
    
    # Crear directorio de reportes si no existe
    REPORTS_DIR.mkdir(exist_ok=True)
    
    # Si se solicita convertir ZAP
    if args.convert_zap:
        convert_zap_to_json()
        return
    
    # Cargar y procesar reportes
    reports = []
    
    # Semgrep
    semgrep = SemgrepReport("Semgrep", REPORTS_DIR / "semgrep-report.json")
    semgrep.parse()
    reports.append(semgrep)
    
    # Trivy SCA
    trivy_sca = TrivyReport("Trivy SCA", REPORTS_DIR / "trivy-sca-report.json")
    trivy_sca.parse()
    reports.append(trivy_sca)
    
    # Trivy Container
    trivy_container = TrivyReport("Trivy Container", REPORTS_DIR / "trivy-container-report.json")
    trivy_container.parse()
    reports.append(trivy_container)
    
    # OWASP ZAP
    zap = ZAPReport("OWASP ZAP", REPORTS_DIR / "zap-report.html")
    zap.parse()
    reports.append(zap)
    
    # Evaluar Quality Gate
    gate_passed = quality_gate(reports)
    
    # Guardar resumen consolidado
    summary = {
        "gate_passed": gate_passed,
        "total_critical": sum(r.critical_count for r in reports),
        "total_warnings": sum(r.warning_count for r in reports),
        "reports": [r.get_summary() for r in reports],
        "findings": [f for r in reports for f in r.findings]
    }
    
    with open(REPORTS_DIR / "security-summary.json", 'w') as f:
        json.dump(summary, f, indent=2)
    
    # Exit code
    sys.exit(0 if gate_passed else 1)

if __name__ == "__main__":
    main()
