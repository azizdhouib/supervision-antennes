<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation API — Swagger UI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@5/favicon-32x32.png" sizes="32x32" />
    <style>
        body { margin: 0; padding: 0; background: #fafafa; font-family: system-ui, -apple-system, sans-serif; }
        .custom-topbar {
            background: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .custom-topbar a {
            color: #f1f5f9;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.15s ease;
        }
        .custom-topbar a:hover {
            color: #f87171;
        }
        .swagger-ui .topbar { display: none; }
    </style>
</head>
<body>
    <div class="custom-topbar">
        <a href="/">
            &larr; Retour au Dashboard de Supervision
        </a>
        <div style="display: flex; align-items: center; gap: 15px; font-size: 12px; color: #94a3b8;">
            <span>Clé API par défaut : <code style="background: #1e293b; padding: 2px 6px; border-radius: 4px; color: #e2e8f0; font-family: monospace;">secret-telecom-api-token-2026</code></span>
            <span>Spécification : <a href="/openapi.yaml" target="_blank" style="color: #38bdf8; text-decoration: underline;">openapi.yaml</a></span>
        </div>
    </div>

    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "/openapi.yaml",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "StandaloneLayout",
                persistAuthorization: true
            });
        };
    </script>
</body>
</html>
