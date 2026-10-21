<?php

declare(strict_types=1);

namespace App\Controllers;

final class ScanController
{
    private const TEMP_DIR = __DIR__ . '/../../temp';
    private const REPORTS_DIR = __DIR__ . '/../../temp/reports';
    private const PROGRESS_FILE = __DIR__ . '/../../temp/progress.json';

    /**
     * Inicia un análisis de seguridad en tiempo real
     */
    public function startScan(): void
    {
        $clonePath = $_POST['clone_path'] ?? '';
        $repoName = $_POST['repo_name'] ?? '';
        
        if (empty($clonePath) || !is_dir($clonePath)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid clone path']);
            return;
        }

        // Crear directorio de reportes
        if (!is_dir(self::REPORTS_DIR)) {
            mkdir(self::REPORTS_DIR, 0755, true);
        }

        // Inicializar archivo de progreso
        $this->initializeProgress($repoName);

        // Ejecutar análisis en background
        $this->runSecurityScan($clonePath, $repoName);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Scan started',
            'scan_id' => $repoName
        ]);
    }

    /**
     * Obtiene el progreso actual del análisis
     */
    public function getProgress(): void
    {
        $scanId = $_GET['scan_id'] ?? '';
        
        if (empty($scanId)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Scan ID is required']);
            return;
        }

        if (!file_exists(self::PROGRESS_FILE)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No progress file found']);
            return;
        }

        $progress = json_decode(file_get_contents(self::PROGRESS_FILE), true);
        
        // Filtrar solo el scan solicitado
        $scanProgress = $progress[$scanId] ?? null;

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'progress' => $scanProgress
        ]);
    }

    /**
     * Inicializa el archivo de progreso
     */
    private function initializeProgress(string $scanId): void
    {
        $progress = [];
        if (file_exists(self::PROGRESS_FILE)) {
            $progress = json_decode(file_get_contents(self::PROGRESS_FILE), true) ?? [];
        }

        $progress[$scanId] = [
            'status' => 'running',
            'current_step' => 'initializing',
            'steps' => [
                'clone' => ['status' => 'completed', 'message' => 'Repository cloned'],
                'semgrep' => ['status' => 'pending', 'message' => 'Waiting to start'],
                'trivy_sca' => ['status' => 'pending', 'message' => 'Waiting to start'],
                'trivy_container' => ['status' => 'pending', 'message' => 'Waiting to start'],
                'zap' => ['status' => 'pending', 'message' => 'Waiting to start'],
                'quality_gate' => ['status' => 'pending', 'message' => 'Waiting to start']
            ],
            'findings' => [
                'semgrep' => ['critical' => 0, 'warning' => 0],
                'trivy_sca' => ['critical' => 0, 'warning' => 0],
                'trivy_container' => ['critical' => 0, 'warning' => 0],
                'zap' => ['critical' => 0, 'warning' => 0]
            ],
            'started_at' => date('Y-m-d H:i:s'),
            'completed_at' => null
        ];

        file_put_contents(self::PROGRESS_FILE, json_encode($progress, JSON_PRETTY_PRINT));
    }

    /**
     * Actualiza el progreso de un paso
     */
    private function updateProgress(string $scanId, string $step, string $status, string $message, array $findings = []): void
    {
        if (!file_exists(self::PROGRESS_FILE)) {
            return;
        }

        $progress = json_decode(file_get_contents(self::PROGRESS_FILE), true);
        
        if (!isset($progress[$scanId])) {
            return;
        }

        $progress[$scanId]['current_step'] = $step;
        $progress[$scanId]['steps'][$step]['status'] = $status;
        $progress[$scanId]['steps'][$step]['message'] = $message;

        if (!empty($findings)) {
            $progress[$scanId]['findings'][$step] = $findings;
        }

        file_put_contents(self::PROGRESS_FILE, json_encode($progress, JSON_PRETTY_PRINT));
    }

    /**
     * Ejecuta el análisis de seguridad
     */
    private function runSecurityScan(string $clonePath, string $scanId): void
    {
        $reportPath = self::REPORTS_DIR . '/' . $scanId;

        // Paso 1: Semgrep (SAST)
        $this->updateProgress($scanId, 'semgrep', 'running', 'Running Semgrep analysis...');
        $semgrepOutput = $this->runSemgrep($clonePath, $reportPath);
        $this->updateProgress($scanId, 'semgrep', 'completed', 'Semgrep analysis completed', $semgrepOutput);

        // Paso 2: Trivy SCA
        $this->updateProgress($scanId, 'trivy_sca', 'running', 'Running Trivy SCA analysis...');
        $trivyScaOutput = $this->runTrivySCA($clonePath, $reportPath);
        $this->updateProgress($scanId, 'trivy_sca', 'completed', 'Trivy SCA analysis completed', $trivyScaOutput);

        // Paso 3: Trivy Container (si hay Dockerfile)
        $this->updateProgress($scanId, 'trivy_container', 'running', 'Running Trivy Container analysis...');
        $trivyContainerOutput = $this->runTrivyContainer($clonePath, $reportPath);
        $this->updateProgress($scanId, 'trivy_container', 'completed', 'Trivy Container analysis completed', $trivyContainerOutput);

        // Paso 4: OWASP ZAP (DAST) - solo si es una app web
        $this->updateProgress($scanId, 'zap', 'running', 'Running OWASP ZAP analysis...');
        $zapOutput = $this->runZAP($clonePath, $reportPath);
        $this->updateProgress($scanId, 'zap', 'completed', 'OWASP ZAP analysis completed', $zapOutput);

        // Paso 5: Quality Gate
        $this->updateProgress($scanId, 'quality_gate', 'running', 'Evaluating Quality Gate...');
        $gateResult = $this->evaluateQualityGate($scanId);
        $this->updateProgress($scanId, 'quality_gate', 'completed', 'Quality Gate evaluation completed');

        // Finalizar
        $progress = json_decode(file_get_contents(self::PROGRESS_FILE), true);
        $progress[$scanId]['status'] = 'completed';
        $progress[$scanId]['completed_at'] = date('Y-m-d H:i:s');
        $progress[$scanId]['gate_passed'] = $gateResult['passed'];
        $progress[$scanId]['gate_message'] = $gateResult['message'];
        file_put_contents(self::PROGRESS_FILE, json_encode($progress, JSON_PRETTY_PRINT));

        // Enviar notificación a Telegram
        $this->sendTelegramNotification($scanId, $gateResult);
    }

    /**
     * Ejecuta Semgrep
     */
    private function runSemgrep(string $path, string $reportPath): array
    {
        $output = [];
        $reportFile = $reportPath . '/semgrep.json';
        
        $command = sprintf(
            'semgrep scan --config=config/semgrep-rules.yml --json --output=%s %s 2>&1',
            escapeshellarg($reportFile),
            escapeshellarg($path)
        );
        
        exec($command, $output, $returnCode);

        $findings = ['critical' => 0, 'warning' => 0];
        
        if (file_exists($reportFile)) {
            $data = json_decode(file_get_contents($reportFile), true);
            $results = $data['results'] ?? [];
            
            foreach ($results as $result) {
                $severity = strtoupper($result['extra']['severity'] ?? 'INFO');
                if (in_array($severity, ['CRITICAL', 'HIGH', 'ERROR'])) {
                    $findings['critical']++;
                } else {
                    $findings['warning']++;
                }
            }
        }

        return $findings;
    }

    /**
     * Ejecuta Trivy SCA
     */
    private function runTrivySCA(string $path, string $reportPath): array
    {
        $output = [];
        $reportFile = $reportPath . '/trivy-sca.json';
        
        $command = sprintf(
            'trivy fs --skip-dirs=vendor --format json --output=%s %s 2>&1',
            escapeshellarg($reportFile),
            escapeshellarg($path)
        );
        
        exec($command, $output, $returnCode);

        $findings = ['critical' => 0, 'warning' => 0];
        
        if (file_exists($reportFile)) {
            $data = json_decode(file_get_contents($reportFile), true);
            $results = $data['Results'] ?? [];
            
            foreach ($results as $result) {
                $vulnerabilities = $result['Vulnerabilities'] ?? [];
                foreach ($vulnerabilities as $vuln) {
                    $severity = strtoupper($vuln['Severity'] ?? 'UNKNOWN');
                    if (in_array($severity, ['CRITICAL', 'HIGH'])) {
                        $findings['critical']++;
                    } else {
                        $findings['warning']++;
                    }
                }
            }
        }

        return $findings;
    }

    /**
     * Ejecuta Trivy Container
     */
    private function runTrivyContainer(string $path, string $reportPath): array
    {
        $dockerfile = $path . '/Dockerfile';
        
        if (!file_exists($dockerfile)) {
            return ['critical' => 0, 'warning' => 0, 'skipped' => true];
        }

        $output = [];
        $reportFile = $reportPath . '/trivy-container.json';
        
        // Intentar construir y escanear imagen
        $imageName = 'scan-temp-' . time();
        
        // Build
        $buildCommand = sprintf('docker build -t %s %s 2>&1', $imageName, $path);
        exec($buildCommand, $output, $buildReturnCode);

        if ($buildReturnCode !== 0) {
            return ['critical' => 0, 'warning' => 0, 'skipped' => true, 'error' => 'Build failed'];
        }

        // Scan
        $scanCommand = sprintf('trivy image --format json --output=%s %s 2>&1', $reportFile, $imageName);
        exec($scanCommand, $output, $scanReturnCode);

        // Cleanup
        exec(sprintf('docker rmi %s 2>&1', $imageName), $output);

        $findings = ['critical' => 0, 'warning' => 0];
        
        if (file_exists($reportFile)) {
            $data = json_decode(file_get_contents($reportFile), true);
            $results = $data['Results'] ?? [];
            
            foreach ($results as $result) {
                $vulnerabilities = $result['Vulnerabilities'] ?? [];
                foreach ($vulnerabilities as $vuln) {
                    $severity = strtoupper($vuln['Severity'] ?? 'UNKNOWN');
                    if (in_array($severity, ['CRITICAL', 'HIGH'])) {
                        $findings['critical']++;
                    } else {
                        $findings['warning']++;
                    }
                }
            }
        }

        return $findings;
    }

    /**
     * Ejecuta OWASP ZAP
     */
    private function runZAP(string $path, string $reportPath): array
    {
        // ZAP requiere que la app esté corriendo, esto es más complejo
        // Por ahora, simulamos que no se ejecuta
        return ['critical' => 0, 'warning' => 0, 'skipped' => true, 'message' => 'ZAP requires running application'];
    }

    /**
     * Evalúa el Quality Gate
     */
    private function evaluateQualityGate(string $scanId): array
    {
        if (!file_exists(self::PROGRESS_FILE)) {
            return ['passed' => false, 'message' => 'No progress file'];
        }

        $progress = json_decode(file_get_contents(self::PROGRESS_FILE), true);
        $findings = $progress[$scanId]['findings'] ?? [];

        // Solo considerar código y dependencias (no container)
        $codeCritical = ($findings['semgrep']['critical'] ?? 0) + ($findings['trivy_sca']['critical'] ?? 0);

        if ($codeCritical > 0) {
            return [
                'passed' => false,
                'message' => 'Quality Gate FAILED: Critical vulnerabilities found in code/dependencies'
            ];
        }

        return [
            'passed' => true,
            'message' => 'Quality Gate PASSED: No critical vulnerabilities in code/dependencies'
        ];
    }

    /**
     * Envía notificación a Telegram
     */
    private function sendTelegramNotification(string $scanId, array $gateResult): void
    {
        $token = '8686986582:AAEvJUgu6tfVFbMQjXfnigmz77v9upVAqM0';
        $chatId = '5993357051';

        if (!file_exists(self::PROGRESS_FILE)) {
            return;
        }

        $progress = json_decode(file_get_contents(self::PROGRESS_FILE), true);
        $scanData = $progress[$scanId] ?? [];

        $findings = $scanData['findings'] ?? [];
        $totalCritical = array_sum(array_column($findings, 'critical'));
        $totalWarning = array_sum(array_column($findings, 'warning'));

        $status = $gateResult['passed'] ? '✅ PASADO' : '❌ FALLADO';
        $message = sprintf(
            "🔍 *Análisis de Seguridad - %s*\n\n".
            "📦 Repositorio: %s\n".
            "📊 Hallazgos:\n".
            "🔴 Críticos: %d\n".
            "🟡 Advertencias: %d\n\n".
            "%s\n\n".
            "%s",
            $scanId,
            $scanId,
            $totalCritical,
            $totalWarning,
            $gateResult['message'],
            $gateResult['passed'] ? 'El código puede avanzar a producción' : 'El código requiere correcciones'
        );

        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $token);
        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($payload),
                'timeout' => 10
            ]
        ]);

        @file_get_contents($url, false, $context);
    }
}
