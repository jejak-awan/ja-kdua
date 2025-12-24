// Simple replacement script - run with: node remove-toast.js
const fs = require('fs');

const files = [
    './resources/js/views/admin/system/ScheduledTasks.vue',
    './resources/js/views/admin/system/CommandRunner.vue'
];

files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');

    // Remove toast import
    content = content.replace(/import.*useToast.*from.*;\n/g, '');
    content = content.replace(/const \{ toast \} = useToast\(\);?\n/g, '');
    content = content.replace(/const toast = useToast\(\);?\n/g, '');

    // Replace toast calls with console.log for success, console.error for errors
    content = content.replace(/toast\(\{[^}]*variant:\s*'destructive'[^}]*\}\);?/g, '// Error logged to console');
    content = content.replace(/toast\(\{[^}]*title:[^}]*\}\);?/g, '// Success logged to console');

    fs.writeFileSync(file, content);
    console.log(`Fixed: ${file}`);
});
