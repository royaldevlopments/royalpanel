<?php

namespace Pterodactyl\Services\Security;

use Illuminate\Support\Facades\Log;

class ServerProtectionService
{
    private string $portHttp = '80,443';

    public function blockIpIptables(string $ip): array
    {
        try {
            $output = shell_exec("iptables -A INPUT -s {$ip} -j DROP 2>&1");
            shell_exec("ip6tables -A INPUT -s {$ip} -j DROP 2>&1");
            Log::info("ServerProtection: Blocked IP {$ip} via iptables");
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function unblockIpIptables(string $ip): array
    {
        try {
            $output = shell_exec("iptables -D INPUT -s {$ip} -j DROP 2>&1");
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function setDefaultDropPolicy(): array
    {
        try {
            shell_exec('iptables -P INPUT DROP 2>&1');
            shell_exec('iptables -P FORWARD DROP 2>&1');
            shell_exec('iptables -P OUTPUT ACCEPT 2>&1');
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function allowEstablished(): array
    {
        try {
            $output = shell_exec('iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT 2>&1');
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function allowLoopback(): array
    {
        try {
            $output = shell_exec('iptables -A INPUT -i lo -j ACCEPT 2>&1');
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function synProxyProtection(): array
    {
        try {
            shell_exec('sysctl -w net.ipv4.tcp_syncookies=1 2>&1');
            shell_exec('sysctl -w net.ipv4.tcp_syn_retries=2 2>&1');
            shell_exec('sysctl -w net.ipv4.tcp_synack_retries=1 2>&1');
            shell_exec('sysctl -w net.ipv4.tcp_max_syn_backlog=65536 2>&1');
            shell_exec('sysctl -w net.core.somaxconn=65536 2>&1');
            shell_exec('sysctl -w net.ipv4.tcp_abort_on_overflow=1 2>&1');
            shell_exec('iptables -t raw -I PREROUTING -p tcp -m tcp --syn -j CT --notrack 2>&1');
            $output = shell_exec('iptables -I INPUT -p tcp -m tcp --syn -m state --state INVALID -j DROP 2>&1');
            shell_exec('iptables -A INPUT -p tcp --syn -m limit --limit 200/second --limit-burst 300 -j ACCEPT 2>&1');
            shell_exec('iptables -A INPUT -p tcp --syn -j DROP 2>&1');
            Log::info('ServerProtection: SYNPROXY + syncookies + kernel tuning enabled');
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function hashlimitPerIp(string $port = '80,443', int $ratePerSecond = 30, int $burst = 50): array
    {
        try {
            $output = shell_exec("iptables -A INPUT -p tcp -m multiport --dports {$port} -m hashlimit --hashlimit-name perip --hashlimit-mode srcip --hashlimit-upto {$ratePerSecond}/second --hashlimit-burst {$burst} --hashlimit-htable-expire 60000 -j ACCEPT 2>&1");
            shell_exec("iptables -A INPUT -p tcp -m multiport --dports {$port} -j DROP 2>&1");
            Log::info("ServerProtection: hashlimit per-IP {$ratePerSecond}/sec burst {$burst} on port {$port}");
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function recentRateLimit(string $port = '80,443', int $hits = 60, int $seconds = 30): array
    {
        try {
            $output = shell_exec("iptables -A INPUT -p tcp -m multiport --dports {$port} -m recent --name DDOS --set 2>&1");
            shell_exec("iptables -A INPUT -p tcp -m multiport --dports {$port} -m recent --name DDOS --update --seconds {$seconds} --hitcount {$hits} -j DROP 2>&1");
            Log::info("ServerProtection: recent module rate-limit {$hits} hits in {$seconds}s on port {$port}");
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function connlimitPerIp(string $port = '80,443', int $maxPerIp = 20): array
    {
        try {
            $output = shell_exec("iptables -A INPUT -p tcp -m multiport --dports {$port} -m connlimit --connlimit-above {$maxPerIp} --connlimit-mask 32 -j DROP 2>&1");
            shell_exec("iptables -A INPUT -p tcp -m multiport --dports {$port} -m connlimit --connlimit-above {$maxPerIp} --connlimit-mask 32 -j LOG --log-prefix 'CONNLIMIT: ' 2>&1");
            Log::info("ServerProtection: connlimit {$maxPerIp} per IP on port {$port}");
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function portStealth(string $port = '80,443'): array
    {
        try {
            $output = shell_exec("iptables -A INPUT -p tcp -m multiport --dports {$port} -m state --state NEW -m recent --name PORTSCAN --set 2>&1");
            shell_exec("iptables -A INPUT -p tcp -m multiport --dports {$port} -m state --state NEW -m recent --name PORTSCAN --update --seconds 10 --hitcount 10 -j DROP 2>&1");
            Log::info('ServerProtection: Port scan protection enabled');
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function invalidPackets(): array
    {
        try {
            $rules = [
                'iptables -A INPUT -m state --state INVALID -j DROP',
                'iptables -A INPUT -p tcp --tcp-flags ALL NONE -j DROP',
                'iptables -A INPUT -p tcp --tcp-flags ALL ALL -j DROP',
                'iptables -A INPUT -p tcp --tcp-flags ALL FIN,URG,PSH -j DROP',
                'iptables -A INPUT -p tcp --tcp-flags ALL SYN,RST,ACK,FIN,URG -j DROP',
                'iptables -A INPUT -p tcp --tcp-flags SYN,RST SYN,RST -j DROP',
                'iptables -A INPUT -p tcp --tcp-flags SYN,FIN SYN,FIN -j DROP',
            ];
            $outputs = [];
            foreach ($rules as $rule) {
                $outputs[] = shell_exec($rule . ' 2>&1');
            }
            Log::info('ServerProtection: Invalid packet dropping rules applied');
            return ['success' => true, 'output' => implode("\n", $outputs)];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function fragmentProtection(): array
    {
        try {
            $output = shell_exec('iptables -A INPUT -f -j DROP 2>&1');
            shell_exec('iptables -A INPUT -p tcp --tcp-flags SYN SYN -m tcpmss --mss 1:500 -j DROP 2>&1');
            Log::info('ServerProtection: Fragment protection enabled');
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function pingFloodProtection(): array
    {
        try {
            shell_exec('sysctl -w net.ipv4.icmp_echo_ignore_broadcasts=1 2>&1');
            shell_exec('sysctl -w net.ipv4.icmp_ignore_bogus_error_responses=1 2>&1');
            $output = shell_exec('iptables -A INPUT -p icmp --icmp-type echo-request -m hashlimit --hashlimit-name ping --hashlimit-mode srcip --hashlimit-upto 5/second --hashlimit-burst 10 -j ACCEPT 2>&1');
            shell_exec('iptables -A INPUT -p icmp --icmp-type echo-request -j DROP 2>&1');
            Log::info('ServerProtection: Ping flood protection enabled');
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function udpFloodProtection(): array
    {
        try {
            $output = shell_exec('iptables -A INPUT -p udp -m hashlimit --hashlimit-name udpflood --hashlimit-mode srcip --hashlimit-upto 50/second --hashlimit-burst 100 -j ACCEPT 2>&1');
            shell_exec('iptables -A INPUT -p udp -j DROP 2>&1');
            Log::info('ServerProtection: UDP flood protection enabled');
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sshProtection(): array
    {
        try {
            $output = shell_exec('iptables -A INPUT -p tcp --dport 22 -m state --state NEW -m recent --name SSH --set 2>&1');
            shell_exec('iptables -A INPUT -p tcp --dport 22 -m state --state NEW -m recent --name SSH --update --seconds 60 --hitcount 5 -j DROP 2>&1');
            shell_exec('iptables -A INPUT -p tcp --dport 22 -j ACCEPT 2>&1');
            Log::info('ServerProtection: SSH brute-force protection enabled');
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function kernelTuning(): array
    {
        try {
            $tweaks = [
                'net.core.rmem_max=134217728', 'net.core.wmem_max=134217728',
                'net.ipv4.tcp_rmem=4096 87380 134217728', 'net.ipv4.tcp_wmem=4096 65536 134217728',
                'net.core.netdev_max_backlog=300000', 'net.ipv4.tcp_congestion_control=bbr',
                'net.core.default_qdisc=fq', 'net.ipv4.tcp_mtu_probing=1',
                'net.ipv4.tcp_fin_timeout=10', 'net.ipv4.tcp_tw_reuse=1',
                'net.ipv4.tcp_max_tw_buckets=2000000', 'net.ipv4.tcp_max_orphans=65536',
                'net.ipv4.ip_local_port_range=1024 65535',
            ];
            foreach ($tweaks as $tweak) {
                shell_exec("sysctl -w {$tweak} 2>&1");
            }
            Log::info('ServerProtection: Kernel network tuning applied');
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function ovhLevelProtection(): array
    {
        return [
            'default_drop' => $this->setDefaultDropPolicy(),
            'loopback' => $this->allowLoopback(),
            'established' => $this->allowEstablished(),
            'kernel_tuning' => $this->kernelTuning(),
            'syn_proxy' => $this->synProxyProtection(),
            'invalid_packets' => $this->invalidPackets(),
            'fragments' => $this->fragmentProtection(),
            'port_scan' => $this->portStealth(),
            'connlimit' => $this->connlimitPerIp($this->portHttp, 20),
            'hashlimit' => $this->hashlimitPerIp($this->portHttp, 30, 50),
            'recent_rate' => $this->recentRateLimit($this->portHttp, 60, 30),
            'ping_flood' => $this->pingFloodProtection(),
            'udp_flood' => $this->udpFloodProtection(),
            'ssh_protect' => $this->sshProtection(),
        ];
    }

    public function enableAllProtection(): array
    {
        return $this->ovhLevelProtection();
    }

    public function rateLimitIptables(string $ip, int $rate = 100): array
    {
        try {
            $output = shell_exec("iptables -A INPUT -s {$ip} -m limit --limit {$rate}/minute -j ACCEPT 2>&1");
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function flushIptables(): array
    {
        try {
            $cmds = [
                'iptables -F', 'iptables -X', 'iptables -Z',
                'iptables -t nat -F', 'iptables -t mangle -F',
                'iptables -P INPUT ACCEPT', 'iptables -P FORWARD ACCEPT', 'iptables -P OUTPUT ACCEPT',
            ];
            $outputs = [];
            foreach ($cmds as $cmd) {
                $outputs[] = shell_exec($cmd . ' 2>&1');
            }
            return ['success' => true, 'output' => implode("\n", $outputs)];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getIptablesRules(): array
    {
        try {
            $output = shell_exec('iptables -L -n -v 2>&1');
            return ['success' => true, 'rules' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
