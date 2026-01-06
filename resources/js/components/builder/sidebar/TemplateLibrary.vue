
<template>
    <Dialog :open="builder.showTemplateLibrary.value" @update:open="val => builder.showTemplateLibrary.value = val">
        <DialogContent class="sm:max-w-5xl h-[85vh] flex flex-col p-0 gap-0 z-[99999] overflow-hidden">
            
            <!-- Save Form Mode -->
            <div v-if="mode === 'save'" class="flex flex-col h-full bg-background">
                 <DialogHeader class="p-6 border-b shrink-0">
                      <DialogTitle>Save Layout as Template</DialogTitle>
                      <DialogDescription>Save your current page structure to reuse later.</DialogDescription>
                 </DialogHeader>
                 <div class="p-6 space-y-6 flex-1 overflow-y-auto">
                     <div class="space-y-2">
                         <Label>Template Name</Label>
                         <Input v-model="saveForm.name" placeholder="E.g. Landing Page V1" class="max-w-md" autofocus />
                     </div>
                     <div class="space-y-2">
                         <Label>Type</Label>
                         <p class="text-sm text-muted-foreground">Currently saving as Full Layout.</p>
                     </div>
                 </div>
                 <div class="p-6 border-t flex justify-end gap-2 bg-muted/10 shrink-0">
                     <Button variant="ghost" @click="mode = 'list'">Cancel</Button>
                     <Button :disabled="saving || !saveForm.name" @click="saveLayout">
                         <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                         Save Template
                     </Button>
                 </div>
            </div>
  
            <!-- List Mode -->
            <div v-else class="flex flex-col h-full overflow-hidden bg-background">
                 <Tabs v-model="activeTab" class="flex flex-col h-full w-full">
                     <div class="px-6 py-4 border-b flex items-center justify-between shrink-0 bg-background z-10 w-full">
                          <div>
                              <DialogTitle>Template Library</DialogTitle>
                              <DialogDescription class="mt-1">Choose a pre-made block or full layout.</DialogDescription>
                          </div>
                          <div class="flex items-center gap-4">
                              <TabsList>
                                  <TabsTrigger value="premade">Premade</TabsTrigger>
                                  <TabsTrigger value="saved">My Templates</TabsTrigger>
                              </TabsList>
                              <Button v-if="activeTab === 'saved'" size="sm" @click="mode = 'save'">
                                  <Plus class="w-4 h-4 mr-2" />
                                  Save Current
                              </Button>
                          </div>
                     </div>
  
                     <div class="flex-1 overflow-hidden relative w-full">
                          <div v-if="loading" class="absolute inset-0 flex items-center justify-center z-10 bg-background/50">
                              <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
                          </div>
  
                          <TabsContent value="premade" class="h-full overflow-y-auto p-6 m-0 w-full data-[state=inactive]:hidden">
                               <div v-if="premadeTemplates.length === 0 && !loading" class="flex flex-col items-center justify-center h-full text-muted-foreground">
                                    <LayoutTemplate class="w-12 h-12 mb-4 opacity-20" />
                                    <p>No premade templates found.</p>
                               </div>
                               <div v-else class="grid grid-cols-2 md:grid-cols-3 gap-6">
                                    <div v-for="tpl in premadeTemplates" :key="tpl.id" class="group relative aspect-[4/3] rounded-xl overflow-hidden border bg-background hover:border-primary transition-all shadow-sm hover:shadow-md flex flex-col">
                                          <div class="flex-1 bg-muted/5 p-6 flex items-center justify-center text-center">
                                              <div>
                                                  <LayoutTemplate class="w-8 h-8 mx-auto mb-3 text-muted-foreground/50" />
                                                  <span class="text-sm font-medium text-foreground">{{ tpl.name }}</span>
                                                  <p v-if="tpl.description" class="text-xs text-muted-foreground mt-1 line-clamp-2">{{ tpl.description }}</p>
                                              </div>
                                          </div>
                                          <div class="p-3 border-t bg-card flex justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                               <Button size="sm" class="w-full" @click="insertTemplate(tpl)">Insert</Button>
                                          </div>
                                    </div>
                               </div>
                          </TabsContent>
  
                          <TabsContent value="saved" class="h-full overflow-y-auto p-6 m-0 w-full data-[state=inactive]:hidden">
                                <div v-if="savedTemplates.length === 0 && !loading" class="flex flex-col items-center justify-center h-full text-muted-foreground space-y-4">
                                    <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center">
                                        <LayoutTemplate class="w-8 h-8 opacity-40" />
                                    </div>
                                    <div class="text-center">
                                        <h3 class="font-medium text-foreground">No templates yet</h3>
                                        <p class="text-sm">Save your current layout to reuse it later.</p>
                                    </div>
                                    <Button variant="outline" @click="mode = 'save'">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Save Current Layout
                                    </Button>
                                </div>
                                <div v-else class="grid grid-cols-2 md:grid-cols-3 gap-6">
                                    <div v-for="tpl in savedTemplates" :key="tpl.id" class="group relative aspect-[4/3] rounded-xl overflow-hidden border bg-background hover:border-primary transition-all shadow-sm hover:shadow-md flex flex-col">
                                          <div class="flex-1 bg-muted/5 p-6 flex items-center justify-center text-center relative">
                                              <span class="font-medium text-sm">{{ tpl.name }}</span>
                                              <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                  <Button size="icon" variant="ghost" class="h-6 w-6 text-destructive hover:bg-destructive/10" @click.stop="confirmDelete(tpl)">
                                                      <Trash2 class="w-3 h-3" />
                                                  </Button>
                                              </div>
                                          </div>
                                          <div class="p-3 border-t bg-card flex justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                               <Button size="sm" class="w-full" @click="insertTemplate(tpl)">Insert</Button>
                                          </div>
                                    </div>
                                </div>
                          </TabsContent>
                     </div>
                 </Tabs>
            </div>
  
        </DialogContent>
    </Dialog>
  </template>
  
  <script setup>
  import { inject, ref, onMounted, watch } from 'vue';
  import { LayoutTemplate, Loader2, Trash2, Plus } from 'lucide-vue-next';
  import { generateUUID } from '../utils';
  import Button from '@/components/ui/button.vue';
  import Dialog from '@/components/ui/dialog.vue';
  import DialogContent from '@/components/ui/dialog-content.vue';
  import DialogHeader from '@/components/ui/dialog-header.vue';
  import DialogTitle from '@/components/ui/dialog-title.vue';
  import DialogDescription from '@/components/ui/dialog-description.vue';
  import Input from '@/components/ui/input.vue';
  import Label from '@/components/ui/label.vue';
  import Tabs from '@/components/ui/tabs.vue';
  import TabsContent from '@/components/ui/tabs-content.vue';
  import TabsList from '@/components/ui/tabs-list.vue';
  import TabsTrigger from '@/components/ui/tabs-trigger.vue';
  import { toast } from '@/services/toast';
  import templateService from '@/services/templateService';
  
  const builder = inject('builder');
  
  // State
  const mode = ref('list'); // 'list' | 'save'
  const activeTab = ref('premade');
  const loading = ref(false);
  const saving = ref(false);
  
  const premadeTemplates = ref([]);
  const savedTemplates = ref([]);
  
  const saveForm = ref({
      name: ''
  });
  
  // Data Fetching
  const fetchTemplates = async () => {
      loading.value = true;
      try {
          // Fetch templates without restrictive filters for maximum visibility
          const response = await templateService.getTemplates({ per_page: 500 });
          
          // Debug log (check browser console if possible)
          console.debug("Templates API Raw Response:", response);

          // Highly resilient data extraction
          let all = [];
          if (response.data) {
              if (Array.isArray(response.data.data)) {
                  all = response.data.data;
              } else if (response.data.data && Array.isArray(response.data.data.data)) {
                  all = response.data.data.data;
              } else if (Array.isArray(response.data)) {
                  all = response.data;
              }
          }

          console.debug("Extracted templates count:", all.length);
          
          // Only show templates that are compatible with the visual builder
          // 'builder' and 'section' types have JSON block data
          // 'page', 'post', 'custom' types have raw HTML (not compatible)
          const builderCompatibleTypes = ['builder', 'section'];
          const compatibleTemplates = all.filter(t => builderCompatibleTypes.includes(t.type));
          
          console.debug("Compatible templates count:", compatibleTemplates.length);
          
          // Separate premade and saved
          premadeTemplates.value = compatibleTemplates.filter(t => !t.author_id);
          savedTemplates.value = compatibleTemplates.filter(t => t.author_id);
          
      } catch (error) {
          console.error("Failed to load templates", error);
          toast.error("Failed to load templates");
      } finally {
          loading.value = false;
      }
  };
  
  // Watch for open
  watch(() => builder.showTemplateLibrary.value, (val) => {
      if (val) {
          fetchTemplates();
          mode.value = 'list';
      }
  });
  
  // Actions
  const saveLayout = async () => {
      if (!saveForm.value.name) return;
      
      saving.value = true;
      try {
          const payload = {
              name: saveForm.value.name,
              slug: saveForm.value.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '') + '-' + Date.now(), // Auto slug
              type: 'builder', // For full layout
              body_template: JSON.stringify(builder.blocks.value), // Serialize blocks
              // title_template? Default?
          };
          
          await templateService.saveTemplate(payload);
          toast.success("Layout saved successfully");
          mode.value = 'list';
          saveForm.value.name = '';
          activeTab.value = 'saved';
          fetchTemplates(); // Refresh
          
      } catch (error) {
          console.error("Save failed", error);
          toast.error("Failed to save template");
      } finally {
          saving.value = false;
      }
  };
  
  const confirmDelete = async (tpl) => {
      if (!confirm(`Delete template "${tpl.name}"?`)) return;
      
      try {
          await templateService.deleteTemplate(tpl.id);
          toast.success("Template deleted");
          // Remove locally
          savedTemplates.value = savedTemplates.value.filter(t => t.id !== tpl.id);
      } catch (error) {
          toast.error("Delete failed");
      }
  };
  
  const insertTemplate = (tpl) => {
      try {
          // Handle body_template as either string or object
          let blocks;
          if (typeof tpl.body_template === 'string') {
              blocks = JSON.parse(tpl.body_template);
          } else {
              blocks = tpl.body_template;
          }
          
          if (!Array.isArray(blocks)) throw new Error("Invalid template format");
          
          // Assign new IDs to avoid conflicts
          const newBlocks = blocks.map(regenerateBlockIds);
          
          builder.blocks.value.push(...newBlocks);
          builder.takeSnapshot();
          
          toast.success("Template inserted successfully!");
          builder.showTemplateLibrary.value = false;
      } catch (error) {
          console.error("Insert failed", error);
          toast.error("Failed to insert template: Invalid data");
      }
  };
  
  const regenerateBlockIds = (block) => {
      const newBlock = { ...block };
      newBlock.id = generateUUID();
      
      // Handle nested blocks in Section
      if (newBlock.settings?.blocks && Array.isArray(newBlock.settings.blocks)) {
          newBlock.settings.blocks = newBlock.settings.blocks.map(regenerateBlockIds);
      }
      
      // Handle nested blocks in Columns
      if (newBlock.settings?.columns && Array.isArray(newBlock.settings.columns)) {
          newBlock.settings.columns = newBlock.settings.columns.map(col => ({
              ...col,
              blocks: Array.isArray(col.blocks) ? col.blocks.map(regenerateBlockIds) : []
          }));
      }
      
      return newBlock;
  };
  </script>
