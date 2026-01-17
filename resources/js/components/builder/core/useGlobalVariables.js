import { ref, computed } from 'vue'

const generateId = () => {
    return 'var_' + Date.now() + '_' + Math.floor(Math.random() * 1000)
}

export function useGlobalVariables() {
    // State
    const globalNumbers = ref([
        { id: generateId(), name: 'Spacing SM', value: '8', unit: 'px' },
        { id: generateId(), name: 'Spacing MD', value: '16', unit: 'px' }
    ])

    const globalText = ref([])
    const globalImages = ref([])
    const globalLinks = ref([])

    const globalColors = ref([
        { id: generateId(), name: 'Primary Color', value: '#2EA3F2', hex: '2EA3F2', opacity: 100 },
        { id: generateId(), name: 'Secondary Color', value: '#2EA3F2', hex: '2EA3F2', opacity: 100 },
        { id: generateId(), name: 'Heading Text Color', value: '#666666', hex: '666666', opacity: 100 },
        { id: generateId(), name: 'Body Text Color', value: '#666666', hex: '666666', opacity: 100 }
    ])

    const globalFonts = ref([
        { id: generateId(), name: 'Headings', family: 'Open Sans' },
        { id: generateId(), name: 'Body', family: 'Open Sans' }
    ])

    // Actions
    const addVariable = (type) => {
        const id = generateId()
        switch (type) {
            case 'numbers':
                globalNumbers.value.push({ id, name: '', value: 'none', unit: '-' })
                break
            case 'text':
                globalText.value.push({ id, name: '', value: '' })
                break
            case 'images':
                globalImages.value.push({ id, name: '', value: null })
                break
            case 'links':
                globalLinks.value.push({ id, name: '', value: '' })
                break
            case 'colors':
                globalColors.value.push({ id, name: '', value: '#000000', hex: '000000', opacity: 100 })
                break
            case 'fonts':
                globalFonts.value.push({ id, name: '', family: 'Open Sans' })
                break
        }
        return id
    }

    const deleteVariable = (index, type) => {
        switch (type) {
            case 'numbers': globalNumbers.value.splice(index, 1); break
            case 'text': globalText.value.splice(index, 1); break
            case 'images': globalImages.value.splice(index, 1); break
            case 'links': globalLinks.value.splice(index, 1); break
            case 'colors': globalColors.value.splice(index, 1); break
            case 'fonts': globalFonts.value.splice(index, 1); break
        }
    }

    // reset/load methods could go here
    const loadVariables = (data) => {
        if (!data) return
        if (data.globalNumbers) globalNumbers.value = data.globalNumbers
        if (data.globalText) globalText.value = data.globalText
        if (data.globalImages) globalImages.value = data.globalImages
        if (data.globalLinks) globalLinks.value = data.globalLinks
        if (data.globalColors) globalColors.value = data.globalColors
        if (data.globalFonts) globalFonts.value = data.globalFonts
    }

    return {
        // State
        globalNumbers,
        globalText,
        globalImages,
        globalLinks,
        globalColors,
        globalFonts,

        // Actions
        addVariable,
        deleteVariable,
        loadVariables
    }
}
