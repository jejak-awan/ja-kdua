import fs from 'fs';
import path from 'path';

// Comprehensive Mapping: Legacy/Alias -> Modern Primary
const preferredMapping = {
    // Common Aliases
    'Home': 'House',
    'HelpCircle': 'CircleHelp',
    'AlertCircle': 'CircleAlert',
    'AlertTriangle': 'TriangleAlert',
    'Loader2': 'LoaderCircle',
    'Edit': 'SquarePen',
    'Edit2': 'Pen',
    'Edit3': 'PenLine',
    'Columns': 'Columns2',
    'Rows': 'Rows2',
    'Layout': 'LayoutDashboard',
    'PlusCircle': 'CirclePlus',
    'CheckCircle': 'CircleCheck',
    'CheckCircle2': 'CircleCheckBig',
    'XCircle': 'CircleX',
    'Grid': 'Grid2X2',
    'MoreHorizontal': 'Ellipsis',
    'Filter': 'ListFilter',
    'Maximize2': 'Maximize',
    'Minimize2': 'Minimize',
    'Sliders': 'SlidersHorizontal',
    'BarChart3': 'ChartColumn',
    'BarChart2': 'ChartNoAxesColumn',
    'BarChart': 'ChartNoAxesColumnIncreasing',
    'AreaChart': 'ChartArea',
    'FileEdit': 'FilePenLine',
    'Table2': 'Table',
    'DropcapIcon': 'Type',
    'UnderlineIcon': 'Underline',

    // Lucide Naming Standards (Icon suffix)
    'CheckIcon': 'Check',
    'ColumnsIcon': 'Columns2',
    'CopyIcon': 'Copy',
    'FileIcon': 'File',
    'FilterIcon': 'ListFilter',
    'FolderIcon': 'Folder',
    'ImageIcon': 'Image',
    'LanguagesIcon': 'Languages',
    'LayoutIcon': 'LayoutDashboard',
    'LinkIcon': 'Link',
    'ListIcon': 'List',
    'MailIcon': 'Mail',
    'MenuIcon': 'Menu',
    'MonitorIcon': 'Monitor',
    'MoveIcon': 'Move',
    'PlusIcon': 'Plus',
    'QuoteIcon': 'Quote',
    'SearchIcon': 'Search',
    'SettingsIcon': 'Settings',
    'SmartphoneIcon': 'Smartphone',
    'StarIcon': 'Star',
    'TableasTableIcon': 'Table',
    'TabletIcon': 'Tablet',
    'TagIcon': 'Tag',
    'TrashIcon': 'Trash2',
    'UnlinkIcon': 'Unlink',
    'UploadIcon': 'Upload',
    'UserIcon': 'User',
    'VideoIcon': 'Video'
};

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let stat = fs.statSync(dirPath);
        if (stat.isDirectory()) {
            walkDir(dirPath, callback);
        } else {
            callback(dirPath);
        }
    });
}

const targetDir = 'resources/js';

console.log(`Starting Audit in ${targetDir}...`);

walkDir(targetDir, (filePath) => {
    if (!filePath.endsWith('.vue') && !filePath.endsWith('.ts')) return;
    if (filePath.includes('node_modules')) return;

    let content = fs.readFileSync(filePath, 'utf8');
    let originalContent = content;

    // 1. Fix combined imports (from previous migrations)
    content = content.replace(/} from 'lucide-vue-next';import/g, "} from 'lucide-vue-next';\nimport");

    // 2. Multiline Import Normalization
    // Match import { ... } from 'lucide-vue-next'; including newlines
    const importRegex = /import \{ ([\s\S]*?) \} from 'lucide-vue-next';/g;

    content = content.replace(importRegex, (match, iconsPart) => {
        const icons = iconsPart
            .split(',')
            .map(i => i.trim())
            .filter(i => i.length > 0);

        const newIcons = icons.map(icon => {
            // Already an alias
            if (icon.includes(' as ')) {
                const [primary, alias] = icon.split(' as ').map(s => s.trim());
                // If the alias is in our preferred list, use the modern primary
                if (preferredMapping[alias]) {
                    return `${preferredMapping[alias]} as ${alias}`;
                }
                return icon;
            }

            // Single name that needs an alias or modernization
            if (preferredMapping[icon]) {
                return `${preferredMapping[icon]} as ${icon}`;
            }

            return icon;
        });

        // Deduplicate and sort for cleanliness
        const uniqueIcons = [...new Set(newIcons)].sort();

        // Return single line for small imports, multiline for large ones
        if (uniqueIcons.length > 5) {
            return `import {\n    ${uniqueIcons.join(',\n    ')}\n} from 'lucide-vue-next';`;
        }
        return `import { ${uniqueIcons.join(', ')} } from 'lucide-vue-next';`;
    });

    if (content !== originalContent) {
        fs.writeFileSync(filePath, content);
        console.log(`Audited & Standardized: ${filePath}`);
    }
});

console.log('Audit Completed.');
