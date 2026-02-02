const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const JS_DIR = path.resolve(__dirname, '../resources/js');

// Helper to find all .vue and .ts files
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
    // Skip logger.ts itself
    if (filePath.endsWith('utils/logger.ts')) return;

    let content = fs.readFileSync(filePath, 'utf8');
    let hasChange = false;

    // 1. Replacements
    if (content.includes('console.log')) {
        content = content.replace(/console\.log/g, 'logger.info');
        hasChange = true;
    }
    if (content.includes('console.warn')) {
        content = content.replace(/console\.warn/g, 'logger.warning');
        hasChange = true;
    }
    if (content.includes('console.error')) {
        content = content.replace(/console\.error/g, 'logger.error');
        hasChange = true;
    }

    if (hasChange) {
        // 2. Add import if missing
        if (!content.includes("@/utils/logger'")) {
            if (filePath.endsWith('.vue')) {
                // Find <script setup lang="ts"> or similar
                const scriptMatch = content.match(/<script[^>]*>/);
                if (scriptMatch) {
                    content = content.replace(scriptMatch[0], `${scriptMatch[0]}\nimport { logger } from '@/utils/logger';`);
                }
            } else {
                // For .ts files, add to the top
                content = `import { logger } from '@/utils/logger';\n${content}`;
            }
        }

        fs.writeFileSync(filePath, content, 'utf8');
        updatedFiles++;
        console.log(`Updated: ${path.relative(process.cwd(), filePath)}`);
    }
});

console.log(`\nFinished! Updated ${updatedFiles} files.`);
