# Intégration MCP (Model Context Protocol)

Ce document explique comment utiliser l'intégration MCP dans ce projet Laravel pour communiquer avec votre serveur Python MCP.

## Configuration

### 1. Variables d'environnement

Ajoutez ces variables à votre fichier `.env` :

```env
MCP_SERVER_URL=http://127.0.0.1:8000
MCP_SERVER_NAME=discuss
MCP_TIMEOUT=30
MCP_RETRY_ATTEMPTS=3
MCP_DEBUG=false
```

### 2. Services disponibles

- **MCPService** : Service principal pour la communication MCP
- **LightsService** : Service mis à jour pour utiliser MCP au lieu de l'API directe

## Utilisation

### Routes de test disponibles

1. **Test de connexion** : `GET /api/mcp/test`
2. **Liste des outils** : `GET /api/mcp/tools`
3. **Contrôle de dispositif** : `POST /api/mcp/device/control`
4. **Appel d'outil générique** : `POST /api/mcp/tool/call`

### Exemples d'utilisation

#### 1. Tester la connexion

```bash
curl http://localhost:9000/api/mcp/test
```

#### 2. Obtenir la liste des outils disponibles

```bash
curl http://localhost:9000/api/mcp/tools
```

#### 3. Contrôler un dispositif

```bash
curl -X POST http://localhost:9000/api/mcp/device/control \
  -H "Content-Type: application/json" \
  -d '{
    "device_name": "salon_light",
    "state": "on"
  }'
```

#### 4. Appeler un outil MCP spécifique

```bash
curl -X POST http://localhost:9000/api/mcp/tool/call \
  -H "Content-Type: application/json" \
  -d '{
    "tool_name": "home_automation_toggle_device",
    "arguments": {
      "device_name": "salon_light",
      "state": "on"
    }
  }'
```

## Utilisation dans le code

### Dans un contrôleur

```php
use App\Services\LightsService;

class HomeController extends Controller
{
    public function toggleLight(Request $request, LightsService $lightsService)
    {
        try {
            $response = $lightsService->manageLightsMCP(
                $request->state,
                $request->device_name
            );

            return response()->json(['success' => true, 'data' => $response]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
```

### Utilisation directe du MCPService

```php
use App\Services\MCPService;

// Injection de dépendance
public function __construct(MCPService $mcpService)
{
    $this->mcpService = $mcpService;
}

// Appeler un outil
$response = $this->mcpService->callTool('tool_name', ['param' => 'value']);

// Obtenir les outils disponibles
$tools = $this->mcpService->getTools();

// Informations sur le serveur
$info = $this->mcpService->getServerInfo();
```

## Serveur Python MCP

Assurez-vous que votre serveur Python MCP fonctionne sur `http://127.0.0.1:8000` avec le nom "discuss" et qu'il expose les outils nécessaires, notamment `home_automation_toggle_device`.

Le serveur doit être compatible avec le protocole MCP et utiliser le transport SSE (Server-Sent Events).

## Logs et débogage

Les logs MCP sont automatiquement écrits dans les logs Laravel. Vous pouvez les voir en utilisant :

```bash
php artisan pail
```

Ou en consultant les fichiers de logs dans `storage/logs/`.

## Migration depuis l'API directe

L'ancienne méthode `manageLights()` dans LightsService est conservée pour la compatibilité, mais il est recommandé d'utiliser `manageLightsMCP()` pour toutes les nouvelles implémentations.

## Dépannage

1. **Erreur de connexion** : Vérifiez que votre serveur Python MCP fonctionne sur la bonne adresse
2. **Outil non trouvé** : Utilisez `/api/mcp/tools` pour voir les outils disponibles
3. **Problèmes de transport** : Vérifiez que votre serveur MCP supporte les SSE
4. **Logs** : Consultez les logs Laravel pour plus de détails sur les erreurs