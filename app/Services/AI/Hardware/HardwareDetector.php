<?php

namespace App\Services\AI\Hardware;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Best-effort, cross-platform hardware probe.
 *
 * It reports available RAM (GB), a coarse CPU description, disk free space (GB)
 * and whether one of the common GPU vendors exposes a device. Everything is
 * defensive: any probe that fails returns null rather than throwing, because the
 * application must never break just because it could not measure the machine.
 *
 * No paid service and no network access is involved.
 */
class HardwareDetector
{
    /**
     * @return array{ram_gb: float|null, cpu: string|null, disk_free_gb: float|null, gpu: string|null, os: string}
     */
    public function detect(): array
    {
        return [
            'ram_gb' => $this->detectRamGb(),
            'cpu' => $this->detectCpu(),
            'disk_free_gb' => $this->detectDiskFreeGb(),
            'gpu' => $this->detectGpu(),
            'os' => PHP_OS_FAMILY,
        ];
    }

    /**
     * Total physical RAM in gigabytes, or null when it cannot be determined.
     */
    public function detectRamGb(): ?float
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                return $this->ramGbWindows();
            }

            if (PHP_OS_FAMILY === 'Linux') {
                return $this->ramGbLinux();
            }

            if (PHP_OS_FAMILY === 'Darwin') {
                return $this->ramGbMac();
            }
        } catch (Throwable $e) {
            Log::debug('HardwareDetector: RAM probe failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    protected function ramGbWindows(): ?float
    {
        // Modern CIM query first: wmic is deprecated and removed on current
        // Windows builds. The full powershell.exe path is used because bare
        // "powershell" is often not resolvable from the PHP/Apache process.
        $output = $this->run($this->powershell('[math]::Round((Get-CimInstance Win32_ComputerSystem).TotalPhysicalMemory/1GB,1)'));

        if ($output && is_numeric(trim($output))) {
            return (float) trim($output);
        }

        // Legacy fallback for older Windows machines that still ship wmic.
        $output = $this->run('wmic computersystem get TotalPhysicalMemory /value');

        if ($output && preg_match('/TotalPhysicalMemory=(\d+)/i', $output, $m)) {
            return round(((int) $m[1]) / 1024 / 1024 / 1024, 1);
        }

        return null;
    }

    /**
     * Build a command that invokes PowerShell via its full path so it resolves
     * reliably regardless of the PATH the web-server process inherited.
     */
    protected function powershell(string $script): string
    {
        $exe = getenv('SystemRoot')
            ? getenv('SystemRoot') . '\\System32\\WindowsPowerShell\\v1.0\\powershell.exe'
            : 'powershell.exe';

        return '"' . $exe . '" -NoProfile -NonInteractive -Command "' . $script . '"';
    }

    protected function ramGbLinux(): ?float
    {
        if (is_readable('/proc/meminfo')) {
            $contents = @file_get_contents('/proc/meminfo');

            if ($contents && preg_match('/MemTotal:\s+(\d+)\s+kB/i', $contents, $m)) {
                return round(((int) $m[1]) / 1024 / 1024, 1);
            }
        }

        return null;
    }

    protected function ramGbMac(): ?float
    {
        $output = $this->run('sysctl -n hw.memsize');

        if ($output && is_numeric(trim($output))) {
            return round(((int) trim($output)) / 1024 / 1024 / 1024, 1);
        }

        return null;
    }

    protected function detectCpu(): ?string
    {
        try {
            if (PHP_OS_FAMILY === 'Linux' && is_readable('/proc/cpuinfo')) {
                $contents = @file_get_contents('/proc/cpuinfo');

                if ($contents && preg_match('/model name\s*:\s*(.+)/i', $contents, $m)) {
                    return trim($m[1]);
                }
            }

            if (PHP_OS_FAMILY === 'Darwin') {
                $output = $this->run('sysctl -n machdep.cpu.brand_string');

                if ($output) {
                    return trim($output);
                }
            }

            if (PHP_OS_FAMILY === 'Windows') {
                $output = $this->run($this->powershell('(Get-CimInstance Win32_Processor | Select-Object -First 1).Name'));

                if ($output) {
                    return trim($output);
                }

                $output = $this->run('wmic cpu get Name /value');

                if ($output && preg_match('/Name=(.+)/i', $output, $m)) {
                    return trim($m[1]);
                }
            }
        } catch (Throwable $e) {
            Log::debug('HardwareDetector: CPU probe failed', ['error' => $e->getMessage()]);
        }

        return php_uname('m') ?: null;
    }

    /**
     * Free disk space on the storage path, in gigabytes.
     */
    public function detectDiskFreeGb(): ?float
    {
        try {
            $bytes = @disk_free_space(storage_path());

            if ($bytes !== false && $bytes !== null) {
                return round($bytes / 1024 / 1024 / 1024, 1);
            }
        } catch (Throwable $e) {
            Log::debug('HardwareDetector: disk probe failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * A coarse GPU label when one can be inferred, otherwise null.
     */
    protected function detectGpu(): ?string
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $output = $this->run($this->powershell('(Get-CimInstance Win32_VideoController | Select-Object -First 1).Name'));

                if ($output) {
                    return trim($output);
                }

                $output = $this->run('wmic path win32_VideoController get Name /value');

                if ($output && preg_match('/Name=(.+)/i', $output, $m)) {
                    return trim($m[1]);
                }
            }

            if (PHP_OS_FAMILY === 'Linux') {
                // nvidia-smi is present whenever the NVIDIA driver is installed.
                $output = $this->run('nvidia-smi --query-gpu=name --format=csv,noheader');

                if ($output && trim($output) !== '') {
                    return trim(explode("\n", trim($output))[0]);
                }
            }

            if (PHP_OS_FAMILY === 'Darwin') {
                $output = $this->run('system_profiler SPDisplaysDataType');

                if ($output && preg_match('/Chipset Model:\s*(.+)/i', $output, $m)) {
                    return trim($m[1]);
                }
            }
        } catch (Throwable $e) {
            Log::debug('HardwareDetector: GPU probe failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    protected function run(string $command): ?string
    {
        // Redirect stderr to the null device so a missing utility never prints
        // noise into the application output (and never leaks into AI responses).
        // (Windows cmd wants 2>NUL, everything else wants 2>/dev/null.)
        $null = PHP_OS_FAMILY === 'Windows' ? '2>NUL' : '2>/dev/null';
        $result = @shell_exec($command . ' ' . $null);

        if (! is_string($result)) {
            return null;
        }

        // Drop the common "not recognized" stderr lines some shells still emit.
        $clean = preg_replace('/^.*is not recognized.*$/mi', '', $result) ?? $result;
        $clean = trim($clean);

        return $clean !== '' ? $clean : null;
    }
}
