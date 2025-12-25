import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import api from '@/services/api';

/**
 * Auto-save composable for content editor
 * 
 * @param {Object} form - Reactive form object
 * @param {String|Number} contentId - Content ID (null for new content)
 * @param {Object} options - Configuration options
 * @returns {Object} Auto-save state and methods
 */
export function useAutoSave(form, contentId = null, options = {}) {
  const {
    interval = 30000, // 30 seconds
    enabled: enabledOption = true,
    onSave = null,
    onError = null,
  } = options;

  // Support both boolean and getter function for enabled
  const enabled = typeof enabledOption === 'function'
    ? computed(enabledOption)
    : (typeof enabledOption === 'object' && enabledOption !== null && 'value' in enabledOption
      ? enabledOption
      : ref(enabledOption));

  const isSaving = ref(false);
  const lastSaved = ref(null);
  const saveStatus = ref('idle'); // idle, saving, saved, error
  const autoSaveInterval = ref(null);
  const hasChanges = ref(false);
  const lastSavedData = ref(null);

  // Track form changes
  const checkChanges = () => {
    if (!lastSavedData.value) {
      hasChanges.value = true;
      return;
    }

    const currentData = JSON.stringify(form.value);
    const savedData = JSON.stringify(lastSavedData.value);
    hasChanges.value = currentData !== savedData;
  };

  // Watch form for changes
  watch(
    () => form.value,
    () => {
      hasChanges.value = true;
      checkChanges();
    },
    { deep: true }
  );

  // Helper to get enabled value
  const getEnabled = () => {
    if (typeof enabled === 'object' && enabled !== null && 'value' in enabled) {
      return enabled.value;
    }
    return enabled;
  };

  // Auto-save function
  const performAutoSave = async () => {
    // Don't save if session terminated, no changes, already saving, or disabled
    if (window.__isSessionTerminated || !hasChanges.value || isSaving.value || !getEnabled()) {
      if (window.__isSessionTerminated) stopAutoSave();
      return;
    }

    // Don't save if form is empty (new content without title)
    if (!form.value.title || form.value.title.trim() === '') {
      return;
    }

    isSaving.value = true;
    saveStatus.value = 'saving';

    try {
      let response;
      const currentContentId = typeof contentId === 'object' && contentId !== null && 'value' in contentId
        ? contentId.value
        : contentId;

      // Prepare payload (tags should already be in form.value if using formWithTags)
      const payload = {
        ...form.value,
        status: 'draft', // Always save as draft for auto-save
      };

      if (currentContentId) {
        // Update existing content
        response = await api.patch(`/admin/cms/contents/${currentContentId}/autosave`, payload);
      } else {
        // Create new draft
        response = await api.post('/admin/cms/contents/autosave', payload);

        // If new content was created, update contentId
        if (response.data?.data?.id) {
          if (typeof contentId === 'object' && contentId !== null && 'value' in contentId) {
            contentId.value = response.data.data.id;
          } else {
            // Can't update primitive contentId, but that's okay
            console.warn('Auto-save created content but contentId is not reactive');
          }
        }
      }

      lastSaved.value = new Date();
      saveStatus.value = 'saved';
      hasChanges.value = false;
      lastSavedData.value = JSON.parse(JSON.stringify(form.value));

      // Callback
      if (onSave) {
        onSave(response.data);
      }

      // Reset status after 3 seconds
      setTimeout(() => {
        if (saveStatus.value === 'saved') {
          saveStatus.value = 'idle';
        }
      }, 3000);
    } catch (error) {
      console.error('Auto-save failed:', error);
      saveStatus.value = 'error';

      // Callback
      if (onError) {
        onError(error);
      }

      // Reset error status after 5 seconds
      setTimeout(() => {
        if (saveStatus.value === 'error') {
          saveStatus.value = 'idle';
        }
      }, 5000);
    } finally {
      isSaving.value = false;
    }
  };

  // Start auto-save interval
  const startAutoSave = () => {
    if (!getEnabled()) return;

    // Initial save after 5 seconds if there are changes
    setTimeout(() => {
      if (hasChanges.value) {
        performAutoSave();
      }
    }, 5000);

    // Then save every interval
    autoSaveInterval.value = setInterval(() => {
      performAutoSave();
    }, interval);
  };

  // Stop auto-save
  const stopAutoSave = () => {
    if (autoSaveInterval.value) {
      clearInterval(autoSaveInterval.value);
      autoSaveInterval.value = null;
    }
  };

  // Manual save trigger
  const saveNow = async () => {
    await performAutoSave();
  };

  // Format last saved time
  const formatLastSaved = () => {
    if (!lastSaved.value) return 'Not saved yet';

    const now = new Date();
    const diff = now - lastSaved.value;
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);

    if (seconds < 10) return 'Just now';
    if (seconds < 60) return `${seconds}s ago`;
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;

    return lastSaved.value.toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  // Initialize
  onMounted(() => {
    lastSavedData.value = JSON.parse(JSON.stringify(form.value));
    if (getEnabled()) {
      startAutoSave();
    }
  });

  // Watch enabled flag if it's reactive
  if (typeof enabled === 'object' && enabled !== null && 'value' in enabled) {
    watch(() => enabled.value, (newValue) => {
      if (newValue && !autoSaveInterval.value) {
        startAutoSave();
      } else if (!newValue && autoSaveInterval.value) {
        stopAutoSave();
      }
    });
  }

  // Cleanup
  onUnmounted(() => {
    stopAutoSave();
  });

  return {
    isSaving,
    lastSaved,
    saveStatus,
    hasChanges,
    saveNow,
    formatLastSaved,
    startAutoSave,
    stopAutoSave,
  };
}

