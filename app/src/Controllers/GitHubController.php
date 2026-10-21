<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\View;

final class GitHubController
{
    private const GITHUB_API_URL = 'https://api.github.com';
    private const TEMP_DIR = __DIR__ . '/../../temp/repos';

    /**
     * Obtiene los repositorios públicos de un usuario de GitHub
     */
    public function getRepositories(): void
    {
        $username = $_GET['username'] ?? '';
        
        if (empty($username)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Username is required']);
            return;
        }

        // Llamar a la API de GitHub
        $url = self::GITHUB_API_URL . '/users/' . urlencode($username) . '/repos';
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: Aginnova-Shield/1.0',
                'timeout' => 10
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to fetch repositories from GitHub']);
            return;
        }

        $repos = json_decode($response, true);
        
        if (!is_array($repos)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid response from GitHub']);
            return;
        }

        // Filtrar información relevante
        $filteredRepos = array_map(function($repo) {
            return [
                'id' => $repo['id'],
                'name' => $repo['name'],
                'full_name' => $repo['full_name'],
                'description' => $repo['description'] ?? 'No description',
                'language' => $repo['language'] ?? 'Unknown',
                'updated_at' => $repo['updated_at'],
                'clone_url' => $repo['clone_url'],
                'size' => $repo['size'],
                'stars' => $repo['stargazers_count']
            ];
        }, $repos);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'repositories' => $filteredRepos]);
    }

    /**
     * Clona un repositorio de GitHub para análisis
     */
    public function cloneRepository(): void
    {
        $username = $_POST['username'] ?? '';
        $repoName = $_POST['repo_name'] ?? '';
        
        if (empty($username) || empty($repoName)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Username and repository name are required']);
            return;
        }

        // Crear directorio temporal si no existe
        if (!is_dir(self::TEMP_DIR)) {
            mkdir(self::TEMP_DIR, 0755, true);
        }

        $clonePath = self::TEMP_DIR . '/' . $username . '-' . $repoName;
        
        // Limpiar si ya existe
        if (is_dir($clonePath)) {
            $this->removeDirectory($clonePath);
        }

        // Clonar el repositorio
        $cloneUrl = 'https://github.com/' . $username . '/' . $repoName . '.git';
        $command = sprintf('git clone --depth 1 %s %s 2>&1', escapeshellarg($cloneUrl), escapeshellarg($clonePath));
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Failed to clone repository',
                'details' => implode("\n", $output)
            ]);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'clone_path' => $clonePath,
            'message' => 'Repository cloned successfully'
        ]);
    }

    /**
     * Elimina un directorio recursivamente
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
