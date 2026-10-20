#!/usr/bin/env python3
"""
Script de notificaciones para Telegram
Envía alertas en vivo sobre el estado del pipeline de seguridad
"""

import json
import os
import sys
from pathlib import Path
import requests

# Configuración
REPORTS_DIR = Path("reports")
SUMMARY_FILE = REPORTS_DIR / "security-summary.json"

# Variables de entorno
TELEGRAM_BOT_TOKEN = os.getenv("TELEGRAM_BOT_TOKEN")
TELEGRAM_CHAT_ID = os.getenv("TELEGRAM_CHAT_ID")

def send_telegram_message(message: str) -> bool:
    """
    Envía un mensaje a través de la API de Telegram
    Retorna True si tiene éxito, False si falla
    """
    if not TELEGRAM_BOT_TOKEN or not TELEGRAM_CHAT_ID:
        print("⚠️  No se configuraron las credenciales de Telegram")
        print("   Se requieren TELEGRAM_BOT_TOKEN y TELEGRAM_CHAT_ID")
        return False
    
    url = f"https://api.telegram.org/bot{TELEGRAM_BOT_TOKEN}/sendMessage"
    
    payload = {
        "chat_id": TELEGRAM_CHAT_ID,
        "text": message,
        "parse_mode": "Markdown"
    }
    
    try:
        response = requests.post(url, json=payload, timeout=10)
        response.raise_for_status()
        print("✅ Mensaje enviado a Telegram")
        return True
    except requests.exceptions.RequestException as e:
        print(f"❌ Error al enviar mensaje a Telegram: {e}")
        return False

def format_alert_message(summary: dict) -> str:
    """
    Formatea el resumen de seguridad como mensaje de Telegram
    """
    gate_passed = summary.get("gate_passed", False)
    total_critical = summary.get("total_critical", 0)
    total_warnings = summary.get("total_warnings", 0)
    
    # Emoji según el estado
    status_emoji = "✅" if gate_passed else "🚨"
    status_text = "PASADO" if gate_passed else "FALLADO"
    
    # Obtener información del repositorio (variables de entorno de GitHub Actions)
    repo_name = os.getenv("GITHUB_REPOSITORY", "Repositorio desconocido")
    branch = os.getenv("GITHUB_REF_NAME", "rama desconocida")
    commit_sha = os.getenv("GITHUB_SHA", "commit desconocido")[:7]
    actor = os.getenv("GITHUB_ACTOR", "usuario desconocido")
    
    # Construir mensaje
    message = f"""
{status_emoji} *DevSecOps Pipeline - {status_text}*

📦 **Repositorio:** {repo_name}
🌿 **Rama:** {branch}
👤 **Autor:** {actor}
🔗 **Commit:** {commit_sha}

📊 *Resumen de Hallazgos:*
🔴 Críticos: {total_critical}
🟡 Advertencias: {total_warnings}
"""
    
    # Agregar detalles por herramienta
    reports = summary.get("reports", [])
    if reports:
        message += "\n🔍 *Detalles por Herramienta:*\n"
        for report in reports:
            tool = report.get("tool", "Desconocido")
            critical = report.get("critical", 0)
            warning = report.get("warning", 0)
            message += f"   • {tool}: {critical} críticos, {warning} advertencias\n"
    
    # Agregar mensaje según el estado
    if gate_passed:
        message += "\n✨ El código puede avanzar a producción"
        if total_critical > 0:
            message += "\n⚠️ Nota: Las vulnerabilidades en la imagen Docker base se reportan pero no bloquean el pipeline"
    else:
        message += "\n⛔ El código NO puede avanzar a producción"
        message += "\n🔧 Por favor revisa y corrige las vulnerabilidades críticas en código/dependencias"
    
    # Agregar URL del workflow
    workflow_url = os.getenv("GITHUB_SERVER_URL", "https://github.com")
    repo = os.getenv("GITHUB_REPOSITORY", "")
    run_id = os.getenv("GITHUB_RUN_ID", "")
    
    if repo and run_id:
        message += f"\n\n📋 [Ver detalles del workflow]({workflow_url}/{repo}/actions/runs/{run_id})"
    
    return message

def format_finding_summary(finding: dict) -> str:
    """
    Formatea un hallazgo individual para Telegram
    """
    tool = finding.get("tool", "Desconocido")
    severity = finding.get("severity", "INFO")
    
    emoji_map = {
        "CRITICAL": "🔴",
        "HIGH": "🔴",
        "MEDIUM": "🟡",
        "WARNING": "🟡",
        "LOW": "🟢",
        "INFO": "🔵"
    }
    emoji = emoji_map.get(severity, "⚪")
    
    # Extraer información según la herramienta
    if tool == "Semgrep":
        message = f"{emoji} [{severity}] {finding.get('message', 'Sin mensaje')}\n"
        message += f"   📁 {finding.get('file', 'N/A')}:{finding.get('line', 'N/A')}"
    elif tool == "Trivy":
        message = f"{emoji} [{severity}] {finding.get('cve', 'N/A')}\n"
        message += f"   📦 {finding.get('package', 'N/A')} {finding.get('version', 'N/A')}\n"
        message += f"   📝 {finding.get('title', 'Sin título')}"
    elif tool == "OWASP ZAP":
        message = f"{emoji} [{severity}] {finding.get('name', 'Alerta desconocida')}"
    else:
        message = f"{emoji} [{severity}] Hallazgo de {tool}"
    
    return message

def send_detailed_alerts(summary: dict):
    """
    Envía alertas detalladas si hay hallazgos críticos
    """
    findings = summary.get("findings", [])
    critical_findings = [f for f in findings if f.get("severity") in ["CRITICAL", "HIGH", "ERROR"]]
    
    if not critical_findings:
        return
    
    # Limitar a los primeros 5 hallazgos críticos para no spam
    critical_findings = critical_findings[:5]
    
    message = "🚨 *HALLAZGOS CRÍTICOS DETECTADOS*\n\n"
    
    for i, finding in enumerate(critical_findings, 1):
        message += f"{i}. {format_finding_summary(finding)}\n\n"
    
    if len(critical_findings) > 5:
        message += f"... y {len(critical_findings) - 5} más"
    
    send_telegram_message(message)

def main():
    """Función principal"""
    print("📤 Iniciando notificación de Telegram...")
    
    # Verificar si existe el resumen
    if not SUMMARY_FILE.exists():
        print(f"⚠️  No se encontró el resumen de seguridad: {SUMMARY_FILE}")
        # Enviar mensaje de error
        message = "⚠️ *DevSecOps Pipeline*\n\nNo se encontró el resumen de seguridad. Posible error en el pipeline."
        send_telegram_message(message)
        sys.exit(1)
    
    # Cargar resumen
    try:
        with open(SUMMARY_FILE, 'r') as f:
            summary = json.load(f)
    except Exception as e:
        print(f"❌ Error al cargar resumen: {e}")
        sys.exit(1)
    
    # Formatear y enviar mensaje principal
    main_message = format_alert_message(summary)
    send_telegram_message(main_message)
    
    # Si hay hallazgos críticos, enviar alertas detalladas
    if not summary.get("gate_passed", False):
        send_detailed_alerts(summary)
    
    print("✅ Notificaciones completadas")

if __name__ == "__main__":
    main()
