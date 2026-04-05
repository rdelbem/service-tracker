import fs from 'fs';
import path from 'path';

// Vite now outputs CSS directly to prod/style.css via assetFileNames config
// This script is kept for compatibility — no action needed
const prodDir = path.join('admin', 'js', 'prod');
if (fs.existsSync(path.join(prodDir, 'style.css'))) {
  console.log('style.css found in prod directory');
} else {
  console.log('WARNING: style.css not found in prod directory');
}
