const fs = require('fs');
const path = require('path');

const buildPath = path.join(__dirname, 'client', 'build');
const indexPath = path.join(buildPath, 'index.html');

console.log('Verifying build...');
console.log('Build path:', buildPath);
console.log('Index path:', indexPath);

if (!fs.existsSync(buildPath)) {
  console.error('ERROR: Build folder does not exist!');
  process.exit(1);
}

if (!fs.existsSync(indexPath)) {
  console.error('ERROR: index.html does not exist!');
  process.exit(1);
}

const files = fs.readdirSync(buildPath);
console.log('✓ Build verified successfully!');
console.log('Files in build folder:', files.length);
console.log('Sample files:', files.slice(0, 5).join(', '));

process.exit(0);

