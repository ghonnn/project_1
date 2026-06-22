<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;

class RouterScriptTemplate extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'variables_schema' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function render(array $variables): string
    {
        $script = $this->template_body;

        foreach ($variables as $key => $value) {
            $script = str_replace('{{'.$key.'}}', (string) $value, $script);
        }

        return $script;
    }

    public static function defaultTemplate(string $scriptType): string
    {
        $service = strtolower($scriptType) === 'hotspot' ? 'hotspot' : 'ppp';
        $aaaLine = $service === 'ppp'
            ? '/ppp aaa set use-radius=yes accounting=yes interim-update={{interim_update}}'
            : '/ip hotspot profile set [ find default=yes ] use-radius=yes radius-accounting=yes radius-interim-update={{interim_update}}';

        return implode("\n", [
            '# NEX OSS/BSS MikroTik {{script_type}} {{os_version}} RADIUS script',
            '# Router: {{router_name}} / {{router_hostname}}',
            '/radius add service='.$service.' address={{radius_server_ip}} secret="{{radius_secret}}" authentication-port={{auth_port}} accounting-port={{acct_port}} timeout=3s',
            '/radius set [find address={{radius_server_ip}}] require-message-auth=no timeout=3s',
            $aaaLine,
            '/system identity set name="{{router_hostname}}"',
        ]);
    }
}
