const fs = require('fs');
const path = require('path');

const JS_DIR = path.resolve(__dirname, '../resources/js');

function getAllFiles(dir, fileList = []) {
    const files = fs.readdirSync(dir);
    files.forEach(file => {
        const filePath = path.join(dir, file);
        if (fs.statSync(filePath).isDirectory()) {
            if (file !== 'node_modules' && file !== 'dist' && file !== 'vendor') {
                getAllFiles(filePath, fileList);
            }
        } else {
            if (filePath.endsWith('.vue') || filePath.endsWith('.ts')) {
                fileList.push(filePath);
            }
        }
    });
    return fileList;
}

const files = getAllFiles(JS_DIR);
let updatedFiles = 0;

files.forEach(filePath => {
    let content = fs.readFileSync(filePath, 'utf8');
    let hasChange = false;

    // 1. Replace catch (e: any) or catch (err: any) or catch (error: any) with unknown
    // This match covers (e: any), (err: any), (error: any)
    const catchAnyRegex = /catch\s*\(\s*(\w+)\s*:\s*any\s*\)/g;

    if (catchAnyRegex.test(content)) {
        content = content.replace(catchAnyRegex, (match, varName) => {
            hasChange = true;
            return `catch (${varName}: unknown)`;
        });
    }

    // 2. Fix logger.error usage in catch blocks if they pass the error object directly
    // This is tricky because we need to find logger.error(e) and change it to logger.error(e instanceof Error ? e.message : String(e), { error: e })
    // We'll search for logger.error(e) where e matches the catch variable
    if (hasChange) {
        // Find the variables used in catch blocks
        const catchVars = [...content.matchAll(/catch\s*\(\s*(\w+)\s*:\s*unknown\s*\)/g)].map(m => m[1]);

        catchVars.forEach(varName => {
            const loggerRegex = new RegExp(`logger\\.error\\(\\s*${varName}\\s*\\)`, 'g');
            if (loggerRegex.test(content)) {
                content = content.replace(loggerRegex, `logger.error(${varName} instanceof Error ? ${varName}.message : String(${varName}), { error: ${varName} })`);
            }
        });
    }

    if (hasChange) {
        fs.writeFileSync(filePath, content, 'utf8');
        updatedFiles++;
        console.log(`Updated: ${path.relative(process.cwd(), filePath)}`);
    }
});

console.log(`\nFinished! Updated ${updatedFiles} files.`);
