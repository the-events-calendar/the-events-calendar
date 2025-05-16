const fs = require('fs');
const path = require('path');

// Function to read all files in a directory recursively
function readDirRecursive(dir) {
	let results = [];
	const list = fs.readdirSync(dir);

	list.forEach(file => {
		file = path.join(dir, file);
		const stat = fs.statSync(file);
		if (stat && stat.isDirectory()) {
			/* Recurse into a subdirectory */
			results = results.concat(readDirRecursive(file));
		} else {
			/* Is a file */
			results.push(file);
		}
	});

	return results;
}

function getImageFiles(imagesDir) {
	return readDirRecursive(imagesDir).filter(filePath => /\.(png|jpg|jpeg|svg)$/i.test(filePath));
}

// Function to get all source files (PCSS, CSS, PHP, JS) in the project
function getSourceFiles(sourceDir) {
	return readDirRecursive(sourceDir).filter(filePath => /\.(pcss|css|php|js)$/i.test(filePath));
}

// Function to check if an image is referenced in any source file
async function checkImageUsage(imageFile, sourceFiles) {
	const imageName = path.basename(imageFile);
	for (const sourceFile of sourceFiles) {
		try {
			const content = fs.readFileSync(sourceFile, 'utf8');
			if (content.includes(imageName)) {
				return true;
			}
		} catch (error) {
			console.error(`Error reading ${sourceFile}:`, error.message);
		}
	}
	return false;
}

// Main function
async function main() {
	if(process.argv.length !== 4) {
		console.log(`Usage: listImagesByUse.js <images_directory> <source_directory>\n`);
		process.exit(1);
	}

	const imagesDir = path.isAbsolute(process.argv[2]) ?
		path.normalize(process.argv[2])
		: path.join(process.cwd(), path.normalize(process.argv[2]));

	const imageFiles = getImageFiles(imagesDir);

	const sourceDir = path.isAbsolute(process.argv[3]) ?
		path.normalize(process.argv[3])
		: path.join(process.cwd(), path.normalize(process.argv[3]));

	const sourceFiles = getSourceFiles(sourceDir);

	for (const imageFile of imageFiles) {
		const imageName = path.basename(imageFile);
		const isUsed = await checkImageUsage(imageFile, sourceFiles);

		if (isUsed) {
			console.log(`\x1b[32mImage "${imageName}" is used.\x1b[0m`);
			for (const sourceFile of sourceFiles) {
				try {
					const content = fs.readFileSync(sourceFile, 'utf8');
					if (content.includes(imageName)) {
						console.log(`  └─ referenced in: ${path.relative(process.cwd(), sourceFile)}`);
					}
				} catch (error) {
					console.error(`Error reading ${sourceFile}:`, error.message);
				}
			}
		} else {
			console.log(`\x1b[31mImage "${imageName}" is NOT used.\x1b[0m`);
			console.log(`  └─ path: ${path.relative(process.cwd(), imageFile)}`);
		}
	}
}

main().catch(error => {
	console.error('An error occurred:', error.message);
});
