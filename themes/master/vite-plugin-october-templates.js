import chokidar from 'chokidar';
import path from 'path';

export default function octoberTemplatesPlugin() {
  return {
    name: 'october-templates-hmr',
    configureServer(server) {
      // Watch OctoberCMS template files
      const templatePaths = [
        path.resolve(process.cwd(), './layouts'),
        path.resolve(process.cwd(), './pages'),
        path.resolve(process.cwd(), './partials'),
        path.resolve(process.cwd(), './content')
      ];

      console.log('🔍 October Templates Plugin iniciado');
      console.log('📂 Vigilando rutas:', templatePaths);

      const watcher = chokidar.watch(templatePaths, {
        ignored: /node_modules/,
        persistent: true,
        ignoreInitial: true,
        depth: 2
      });

      console.log('👀 Watcher configurado correctamente');

      watcher.on('change', (filePath) => {
        console.log(`🔄 Template changed: ${path.relative(process.cwd(), filePath)}`);
        
        // Trigger full page reload for template changes
        server.ws.send({
          type: 'full-reload'
        });
      });

      watcher.on('add', (filePath) => {
        console.log(`➕ Template added: ${path.relative(process.cwd(), filePath)}`);
        server.ws.send({
          type: 'full-reload'
        });
      });

      watcher.on('unlink', (filePath) => {
        console.log(`➖ Template removed: ${path.relative(process.cwd(), filePath)}`);
        server.ws.send({
          type: 'full-reload'
        });
      });

      watcher.on('error', (error) => {
        console.error('❌ Watcher error:', error);
      });

      watcher.on('ready', () => {
        console.log('✅ Watcher ready');
      });

      // Clean up on server close
      server.httpServer?.on('close', () => {
        watcher.close();
      });
    }
  };
}