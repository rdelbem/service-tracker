import fs from 'fs';
import path from 'path';

const assetsDir = path.join('admin', 'js', 'prod', 'assets');
const prodDir = path.join('admin', 'js', 'prod');

if (fs.existsSync(assetsDir)) {
  const cssFiles = fs.readdirSync(assetsDir).filter(f => f.endsWith('.css'));
  
  cssFiles.forEach(file => {
    const src = path.join(assetsDir, file);
    // Rename to a simpler name
    const dest = path.join(prodDir, 'style.css');
    fs.copyFileSync(src, dest);
    console.log(`Copied ${file} to style.css`);
  });
  
  // Clean up assets directory
  fs.rmSync(assetsDir, { recursive: true });
  console.log('Cleaned up assets directory');
} else {
  console.log('No assets directory found');
}
