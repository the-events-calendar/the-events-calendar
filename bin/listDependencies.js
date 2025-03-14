const fs = require('fs');
const path = require('path');

function readPackageJson() {
  const packageJsonPath = process.cwd() + '/package.json';
  try {
    const rawdata = fs.readFileSync(packageJsonPath);
    return JSON.parse(rawdata);
  } catch (error) {
    console.error(`Error reading package.json:`, error.message);
    process.exit(1);
  }
}

function listDependencies(type) {
  const packageJson = readPackageJson();

  let dependencies;
  switch (type) {
    case 'all':
      dependencies = { ...packageJson.dependencies, ...packageJson.devDependencies };
      break;
    case 'dev':
      dependencies = packageJson.devDependencies || {};
      break;
    case 'prod':
      dependencies = packageJson.dependencies || {};
      break;
    default:
      console.error('Invalid type specified. Use "all", "dev", or "prod".');
      process.exit(1);
  }

  Object.keys(dependencies).forEach(dependency => {
    console.log(dependency);
  });
}

async function main() {
  const args = process.argv.slice(2);

  if (args.length === 0) {
    console.error('Usage: node list-dependencies.js [all|dev|prod]');
    process.exit(1);
  }

  const type = args[0].toLowerCase();

  listDependencies(type);
}

main().catch(error => {
  console.error('An error occurred:', error.message);
});
