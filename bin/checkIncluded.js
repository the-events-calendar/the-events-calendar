const fs = require('fs');
const path = require('path');

async function readPackageJson(packageName) {
  try {
    const packagePath = require.resolve(`${packageName}/package.json`);
    const rawdata = fs.readFileSync(packagePath);
    return JSON.parse(rawdata);
  } catch (error) {
    console.error(`Error reading ${packageName} package.json:`, error.message);
    process.exit(1);
  }
}

async function isDependencyOf(package1, package2) {
  try {
    const package1Json = await readPackageJson(package1);
    const dependencies = {
      ...package1Json.dependencies,
      ...package1Json.devDependencies,
      ...package1Json.peerDependencies
    };

    return dependencies.hasOwnProperty(package2);
  } catch (error) {
    console.error('An error occurred:', error.message);
    process.exit(1);
  }
}

async function main() {
  const args = process.argv.slice(2);

  if (args.length !== 2) {
    console.error('Usage: node checkIncluded package_1 package_2');
    process.exit(1);
  }

  const [package1, package2] = args;

  const isDependency = await isDependencyOf(package1, package2);

  if (isDependency) {
    console.log(`${package2} is a dependency of ${package1}.`);
  } else {
    console.log(`${package2} is NOT a dependency of ${package1}.`);
  }
}

main().catch(error => {
  console.error('An error occurred:', error.message);
});