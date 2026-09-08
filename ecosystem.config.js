module.exports = {
    apps: [{
        name: 'wa-agent',
        script: 'wa-agent.js',
        cwd: './wa-desktop-agent',
        
        // Auto-restart jika crash
        autorestart: true,
        max_restarts: 10,
        min_uptime: '10s',
        restart_delay: 5000,       // Tunggu 5 detik sebelum restart
        
        // Monitoring
        watch: false,              // Jangan watch file changes (pakai manual restart)
        max_memory_restart: '500M', // Restart jika memory > 500MB
        
        // Logging (PM2 log, supplement agent's own logging)
        error_file: './wa-desktop-agent/logs/pm2-error.log',
        out_file: './wa-desktop-agent/logs/pm2-out.log',
        merge_logs: true,
        log_date_format: 'YYYY-MM-DD HH:mm:ss',
        
        // Environment
        env: {
            NODE_ENV: 'production',
            WA_API_URL: 'http://localhost:8000',
            WA_AGENT_TOKEN: 'change-this-token-to-something-secure',
            WA_DELAY_MIN: '20',
            WA_DELAY_MAX: '40',
        },
    }],
};
