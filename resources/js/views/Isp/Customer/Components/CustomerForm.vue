<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="$emit('cancel')" class="rounded-full h-8 w-8">
                    <ChevronLeft class="w-4 h-4" />
                </Button>
                <h2 class="text-xl font-bold tracking-tight">{{ title }}</h2>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" @click="$emit('cancel')" class="rounded-xl border-border/60">
                    {{ t('common.actions.cancel') }}
                </Button>
                <Button @click="save" :disabled="loading" class="auth-button-gradient rounded-xl px-6">
                    <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                    {{ t('common.actions.save') }}
                </Button>
            </div>
        </div>

        <Card class="p-0 overflow-hidden auth-card-premium animate-in fade-in zoom-in-95 duration-500">
            <Accordion type="single" collapsible v-model="activeAccordion" class="w-full">
                <!-- 1. Identity Section -->
                <AccordionItem value="identity" class="border-b-0">
                    <AccordionTrigger class="px-6 py-4 hover:no-underline hover:bg-muted/30 transition-colors data-[state=open]:bg-muted/50 data-[state=open]:border-b">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-primary/10 rounded-lg">
                                <User class="w-4 h-4 text-primary" />
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm font-bold tracking-tight">{{ t('isp.billing.customers_manager.tabs.identity') }}</h3>
                                <p class="text-[10px] text-muted-foreground font-medium tracking-tight">{{ t('common.labels.name') }}, {{ t('common.labels.email') }}, {{ t('common.labels.phone') }}</p>
                            </div>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="p-6 space-y-6 animate-in fade-in slide-in-from-top-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground tracking-tight">
                                        {{ t('isp.billing.customers_manager.fields.name') }} <span class="text-destructive">*</span>
                                    </Label>
                                    <Input v-model="form.name" :placeholder="t('isp.billing.customers_manager.fields.name')" class="h-11 rounded-xl" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground tracking-tight">
                                        {{ t('isp.billing.customers_manager.fields.email') }} <span class="text-destructive">*</span>
                                    </Label>
                                    <Input type="email" v-model="form.email" :disabled="isEdit" :placeholder="t('isp.billing.customers_manager.fields.email')" class="h-11 rounded-xl" />
                                </div>
                                <div class="space-y-2" v-if="!isEdit">
                                    <Label class="text-xs font-bold text-muted-foreground tracking-tight">
                                        {{ t('isp.billing.customers_manager.fields.password') }} <span class="text-destructive">*</span>
                                    </Label>
                                    <Input type="password" v-model="form.password" class="h-11 rounded-xl" />
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.phone') }}</Label>
                                    <Input v-model="form.phone" :placeholder="t('isp.billing.customers_manager.fields.phone')" class="h-11 rounded-xl" />
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="space-y-2">
                                        <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.identity_type') }}</Label>
                                        <Select v-model="form.identity_type">
                                            <SelectTrigger class="h-11 rounded-xl">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="KTP">KTP</SelectItem>
                                                <SelectItem value="SIM">SIM</SelectItem>
                                                <SelectItem value="Passport">Passport</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="space-y-2 col-span-2">
                                        <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.identity_number') }}</Label>
                                        <Input v-model="form.identity_number" class="h-11 rounded-xl" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <!-- 2. Address Section -->
                <AccordionItem value="address" class="border-b-0">
                    <AccordionTrigger class="px-6 py-4 border-t hover:no-underline hover:bg-muted/30 transition-colors data-[state=open]:bg-muted/50 data-[state=open]:border-b">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-500/10 rounded-lg">
                                <MapPin class="w-4 h-4 text-blue-500" />
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm font-bold tracking-tight">{{ t('isp.billing.customers_manager.tabs.address') }}</h3>
                                <p class="text-[10px] text-muted-foreground font-medium tracking-tight">{{ t('isp.billing.customers_manager.headers.location_selection') }}</p>
                            </div>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="p-6 animate-in fade-in slide-in-from-top-2">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div class="p-4 bg-muted/30 rounded-2xl border border-dashed border-border/60">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2 col-span-2">
                                            <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.address_street') }} <span class="text-destructive">*</span></Label>
                                            <Textarea v-model="form.address_street" rows="3" class="rounded-xl resize-none" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.address_province') }}</Label>
                                            <Select v-model="selectedProvinceId">
                                                <SelectTrigger class="h-11 rounded-xl">
                                                    <SelectValue :placeholder="t('isp.billing.customers_manager.placeholders.select_province')" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="p in provinces" :key="p.id" :value="p.id">{{ p.name }}</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-xs font-bold text-muted-foreground tracking-tight" :class="!selectedProvinceId && 'text-muted-foreground/40'">{{ t('isp.billing.customers_manager.fields.address_city') }}</Label>
                                            <Select v-model="selectedRegencyId" :disabled="!selectedProvinceId">
                                                <SelectTrigger class="h-11 rounded-xl">
                                                    <SelectValue :placeholder="t('isp.billing.customers_manager.placeholders.select_city')" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="r in regencies" :key="r.id" :value="r.id">{{ r.name }}</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-xs font-bold text-muted-foreground tracking-tight" :class="!selectedRegencyId && 'text-muted-foreground/40'">{{ t('isp.billing.customers_manager.fields.address_district') }}</Label>
                                            <Select v-model="selectedDistrictId" :disabled="!selectedRegencyId">
                                                <SelectTrigger class="h-11 rounded-xl">
                                                    <SelectValue :placeholder="t('isp.billing.customers_manager.placeholders.select_district')" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-xs font-bold text-muted-foreground tracking-tight" :class="!selectedDistrictId && 'text-muted-foreground/40'">{{ t('isp.billing.customers_manager.fields.address_village') }}</Label>
                                            <Select v-model="selectedVillageId" :disabled="!selectedDistrictId">
                                                <SelectTrigger class="h-11 rounded-xl">
                                                    <SelectValue :placeholder="t('isp.billing.customers_manager.placeholders.select_village')" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="v in villages" :key="v.id" :value="v.id">{{ v.name }}</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.address_postal_code') }}</Label>
                                            <Input v-model="form.address_postal_code" class="h-11 rounded-xl" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between mb-2">
                                    <Label class="text-xs font-bold text-muted-foreground tracking-tight">
                                        {{ t('isp.billing.customers_manager.headers.location_selection') }}
                                    </Label>
                                    <div class="flex items-center gap-2">
                                        <button 
                                            type="button"
                                            @click="getCurrentLocation"
                                            :disabled="fetchingLocation"
                                            class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary hover:bg-primary/20 transition-all text-[10px] font-bold border border-primary/20 disabled:opacity-50"
                                        >
                                            <Loader2 v-if="fetchingLocation" class="w-3 h-3 animate-spin" />
                                            <MapPin v-else class="w-3 h-3" />
                                            {{ fetchingLocation ? t('isp.billing.customers_manager.labels.fetching_location') : t('isp.billing.customers_manager.labels.use_current_location') }}
                                        </button>
                                        <span class="text-[10px] font-mono bg-primary/10 text-primary px-2 py-1 rounded-full border border-primary/20">
                                            {{ form.coordinates || t('isp.billing.customers_manager.labels.no_coordinates') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="relative h-[300px] rounded-2xl overflow-hidden border-2 border-muted shadow-inner bg-muted/10 group ring-1 ring-border/40">
                                    <div ref="mapContainer" id="form-map" class="w-full h-full z-10 transition-opacity duration-700"></div>
                                    <div class="absolute inset-x-0 bottom-4 flex justify-center z-[400] pointer-events-none opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                                        <div class="bg-black/70 backdrop-blur-md text-white text-[9px] font-bold px-3 py-1.5 rounded-full shadow-2xl border border-white/10 tracking-tight">
                                            {{ t('isp.billing.customers_manager.labels.map_instruction') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <!-- 3. Internet & Network Section -->
                <AccordionItem value="internet" class="border-b-0">
                    <AccordionTrigger class="px-6 py-4 border-t hover:no-underline hover:bg-muted/30 transition-colors data-[state=open]:bg-muted/50 data-[state=open]:border-b">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-500/10 rounded-lg">
                                <Globe class="w-4 h-4 text-green-500" />
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm font-bold tracking-tight">{{ t('isp.billing.customers_manager.tabs.internet') }}</h3>
                                <p class="text-[10px] text-muted-foreground font-medium tracking-tight">{{ t('isp.billing.customers_manager.fields.router') }}, {{ t('isp.billing.customers_manager.fields.server') }}, {{ t('isp.billing.customers_manager.headers.mikrotik_auth') }}</p>
                            </div>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="p-6 animate-in fade-in slide-in-from-top-2">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                             <!-- Network Config -->
                             <div class="md:col-span-2 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.partner') }}</Label>
                                        <Select v-model="form.partner_id_str" :disabled="fetchingResource">
                                            <SelectTrigger class="h-11 rounded-xl">
                                                <SelectValue :placeholder="fetchingResource ? t('common.loading') : t('isp.billing.customers_manager.placeholders.select_partner')" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="">{{ t('isp.billing.customers_manager.options.none') }}</SelectItem>
                                                <SelectItem v-for="p in partners" :key="p.id" :value="String(p.id)">{{ p.name }}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.router') }}</Label>
                                        <Select v-model="form.router_id_str" :disabled="fetchingResource">
                                            <SelectTrigger class="h-11 rounded-xl">
                                                <SelectValue :placeholder="fetchingResource ? t('common.loading') : t('isp.billing.customers_manager.placeholders.select_router')" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="r in routers" :key="r.id" :value="String(r.id)">{{ r.name }} ({{ r.ip_address }})</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.server') }}</Label>
                                        <Select v-model="form.server_id_str" :disabled="fetchingResource">
                                            <SelectTrigger class="h-11 rounded-xl">
                                                <SelectValue :placeholder="fetchingResource ? t('common.loading') : t('isp.billing.customers_manager.placeholders.select_server')" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="s in servers" :key="s.id" :value="String(s.id)">{{ s.name }}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.service_category') }}</Label>
                                        <Select v-model="form.service_category">
                                            <SelectTrigger class="h-11 rounded-xl">
                                                <SelectValue :placeholder="t('isp.billing.customers_manager.placeholders.select_category')" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="Fiber">{{ t('isp.billing.customers_manager.options.fiber') }}</SelectItem>
                                                <SelectItem value="Wireless">{{ t('isp.billing.customers_manager.options.wireless') }}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="space-y-2 col-span-2">
                                        <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.address_list') }}</Label>
                                        <Input v-model="form.address_list" :placeholder="t('isp.billing.customers_manager.placeholders.address_list')" class="h-11 rounded-xl" />
                                    </div>
                                </div>

                                <div class="bg-muted/30 p-6 rounded-2xl border border-border/50">
                                    <h3 class="text-sm font-bold flex items-center gap-2 mb-4">
                                        <ShieldCheck class="w-4 h-4 text-primary" />
                                        {{ t('isp.billing.customers_manager.headers.mikrotik_auth') }}
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <Label class="text-xs font-bold text-muted-foreground/70 tracking-tight">{{ t('isp.billing.customers_manager.fields.mikrotik_login') }}</Label>
                                            <Input v-model="form.mikrotik_login" placeholder="username" class="h-11 rounded-xl" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-xs font-bold text-muted-foreground/70 tracking-tight">{{ t('isp.billing.customers_manager.fields.mikrotik_password') }}</Label>
                                            <div class="relative">
                                                <Input :type="showMikrotikPass ? 'text' : 'password'" v-model="form.mikrotik_password" class="h-11 rounded-xl pr-12" />
                                                <Button 
                                                    type="button" 
                                                    variant="ghost" 
                                                    size="sm" 
                                                    class="absolute right-0 top-0 h-full px-3"
                                                    @click="showMikrotikPass = !showMikrotikPass"
                                                >
                                                    <Eye v-if="!showMikrotikPass" class="w-4 h-4" />
                                                    <EyeOff v-else class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             </div>

                             <!-- Usage Sidebar (only in Edit) -->
                             <div class="space-y-6">
                                <div v-if="isEdit && (customer?.customer || customer?.isp_customer)" class="relative p-6 rounded-2xl border border-primary/20 bg-linear-to-br from-primary/10 via-background to-background dark:from-primary/20 dark:via-zinc-950 overflow-hidden group shadow-lg shadow-primary/5">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center gap-2">
                                            <div class="p-2 bg-primary/20 rounded-lg">
                                                <Gauge class="w-5 h-5 text-primary" />
                                            </div>
                                            <h3 class="font-bold text-[10px] tracking-widest text-primary">{{ t('isp.billing.customers_manager.headers.usage_monitor') }}</h3>
                                        </div>
                                        <div v-if="(customer?.customer || customer?.isp_customer)?.is_fup_active" class="flex items-center gap-1.5 px-2 py-0.5 bg-amber-500 text-white rounded-full text-[8px] font-black tracking-widest shadow-xl shadow-amber-500/30 animate-pulse">
                                            Fup Active
                                        </div>
                                    </div>
                                    <div class="space-y-5">
                                        <div class="flex justify-between items-end">
                                            <div class="flex flex-col">
                                                <span class="text-3xl font-black tracking-tighter leading-none text-foreground">
                                                    {{ Math.round(((customer?.customer || customer?.isp_customer)?.current_usage_bytes || 0) / (1024 * 1024 * 1024)) }}
                                                    <span class="text-xs font-bold text-muted-foreground ml-1">GB</span>
                                                </span>
                                                <span class="text-[9px] font-black text-muted-foreground tracking-widest mt-1">
                                                    {{ t('isp.billing.customers_manager.labels.total_data') }}
                                                </span>
                                            </div>
                                            <div class="h-8 w-8 bg-muted/30 rounded-full flex items-center justify-center border border-border/40">
                                                <TrendingUp class="w-4 h-4 text-muted-foreground" />
                                            </div>
                                        </div>
                                        <div class="space-y-2 pt-2">
                                            <div class="flex justify-between text-[9px] font-black tracking-widest">
                                                <span class="text-muted-foreground font-bold">{{ t('isp.billing.customers_manager.labels.fup_progress') }}</span>
                                                <span :class="usagePercentage >= 90 ? 'text-destructive' : 'text-primary'" class="font-mono">{{ usagePercentage }}%</span>
                                            </div>
                                            <div class="w-full bg-muted h-2 rounded-full overflow-hidden border border-border/20 shadow-inner">
                                                <div 
                                                    class="h-full rounded-full transition-all duration-1000 ease-out" 
                                                    :class="usagePercentage >= 90 ? 'bg-linear-to-r from-destructive to-red-600' : usagePercentage >= 75 ? 'bg-linear-to-r from-amber-500 to-orange-500' : 'bg-linear-to-r from-primary to-blue-500'"
                                                    :style="{ width: `${usagePercentage}%` }"
                                                ></div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 pt-4 border-t border-border/60">
                                            <Calendar class="w-3 h-3 text-muted-foreground" />
                                            <span class="text-[9px] font-bold text-muted-foreground/80 lowercase italic">
                                                {{ t('isp.billing.customers_manager.labels.cycle_ends') }} {{ cycleEndDateLabel }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-muted/10 p-5 rounded-2xl border border-border/60 shadow-sm backdrop-blur-xs">
                                    <h3 class="text-[9px] font-black tracking-widest text-muted-foreground mb-4 flex items-center gap-2">
                                        <div class="w-1 h-1 bg-primary rounded-full"></div>
                                        {{ t('isp.billing.customers_manager.headers.network_summary') }}
                                    </h3>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center text-[11px]">
                                            <span class="text-muted-foreground font-medium">{{ t('isp.billing.customers_manager.labels.ipv4') }}</span>
                                            <span class="font-mono font-bold bg-muted px-2 py-0.5 rounded border border-border/40">{{ (customer?.customer || customer?.isp_customer)?.ip_address || t('isp.billing.customers_manager.labels.not_assigned') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-[11px]">
                                            <span class="text-muted-foreground font-medium">{{ t('isp.billing.customers_manager.labels.service_date') }}</span>
                                            <span class="font-bold text-foreground">{{ (customer?.customer || customer?.isp_customer)?.installation_date || t('isp.billing.customers_manager.labels.pending') }}</span>
                                        </div>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <!-- 4. Plan & Financial Section -->
                <AccordionItem value="billing" class="border-b-0">
                    <AccordionTrigger class="px-6 py-4 border-t hover:no-underline hover:bg-muted/30 transition-colors data-[state=open]:bg-muted/50 data-[state=open]:border-b">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-amber-500/10 rounded-lg">
                                <CreditCard class="w-4 h-4 text-amber-500" />
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm font-bold tracking-tight">{{ t('isp.billing.customers_manager.tabs.billing') }}</h3>
                                <p class="text-[10px] text-muted-foreground font-medium tracking-tight">{{ t('isp.billing.customers_manager.fields.status') }}, {{ t('isp.billing.customers_manager.fields.billing_due_date') }}, {{ t('isp.billing.customers_manager.fields.billing_notes') }}</p>
                            </div>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="p-6 animate-in fade-in slide-in-from-top-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <Label class="text-xs font-bold text-muted-foreground tracking-tight">
                                        {{ t('isp.billing.customers_manager.fields.status') }}
                                    </Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger class="h-11 rounded-xl">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">{{ t('isp.billing.customers_manager.options.status.active') }}</SelectItem>
                                            <SelectItem value="isolated">{{ t('isp.billing.customers_manager.options.status.isolated') }}</SelectItem>
                                            <SelectItem value="inactive">{{ t('isp.billing.customers_manager.options.status.inactive') }}</SelectItem>
                                            <SelectItem value="suspended">{{ t('isp.billing.customers_manager.options.status.suspended') }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.billing_due_date') }}</Label>
                                        <Input type="number" v-model="form.billing_due_date" min="1" max="28" :placeholder="t('isp.billing.customers_manager.placeholders.billing_due_date')" class="h-11 rounded-xl" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.unique_code') }}</Label>
                                        <Input type="number" v-model="form.unique_code" class="h-11 rounded-xl" />
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 p-4 bg-muted/10 rounded-xl border border-border/60">
                                    <Checkbox id="tax" :checked="form.is_taxed" @update:checked="form.is_taxed = $event" class="rounded" />
                                    <div class="grid gap-1.5 leading-none">
                                        <Label for="tax" class="text-sm font-bold leading-none cursor-pointer">{{ t('isp.billing.customers_manager.fields.is_taxed') }}</Label>
                                        <p class="text-[10px] text-muted-foreground font-semibold">
                                            {{ t('isp.billing.customers_manager.labels.tax_description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.fields.billing_notes') }}</Label>
                                <Textarea v-model="form.billing_notes" class="rounded-2xl h-[230px] resize-none" />
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <!-- 5. Initial Invoice Section -->
                <AccordionItem value="invoice" class="border-b-0">
                    <AccordionTrigger class="px-6 py-4 border-t hover:no-underline hover:bg-muted/30 transition-colors data-[state=open]:bg-muted/50 data-[state=open]:border-b">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-purple-500/10 rounded-lg">
                                <ReceiptText class="w-4 h-4 text-purple-500" />
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm font-bold tracking-tight">{{ t('isp.billing.customers_manager.tabs.invoice') }}</h3>
                                <p class="text-[10px] text-muted-foreground font-medium tracking-tight">{{ t('isp.billing.customers_manager.invoice_items.title') }}, {{ t('isp.billing.customers_manager.invoice_items.fields.total') }}</p>
                            </div>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="p-6 animate-in fade-in slide-in-from-top-2">
                         <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-base font-bold tracking-tight">{{ t('isp.billing.customers_manager.invoice_items.title') }}</h3>
                                    <p class="text-[10px] font-bold text-muted-foreground tracking-tight">{{ t('isp.billing.customers_manager.invoice_items.subtitle') }}</p>
                                </div>
                                <Button type="button" size="sm" variant="outline" @click="addInvoiceItem" class="rounded-xl border-border/60">
                                    <Plus class="w-4 h-4 mr-2" />
                                    {{ t('isp.billing.customers_manager.invoice_items.add_item') }}
                                </Button>
                            </div>

                            <div class="rounded-2xl border border-border/60 overflow-hidden shadow-sm">
                                <table class="w-full text-[13px]">
                                    <thead class="bg-muted/50 border-b border-border/60 text-muted-foreground">
                                        <tr>
                                            <th class="p-3 text-left font-bold text-[10px] tracking-tight">{{ t('isp.billing.customers_manager.invoice_items.fields.name') }}</th>
                                            <th class="p-3 text-right font-bold text-[10px] tracking-tight w-40">{{ t('isp.billing.customers_manager.invoice_items.fields.price') }}</th>
                                            <th class="p-3 text-center font-bold text-[10px] tracking-tight w-24">{{ t('isp.billing.customers_manager.invoice_items.fields.qty') }}</th>
                                            <th class="p-3 text-right font-bold text-[10px] tracking-tight w-40">{{ t('isp.billing.customers_manager.invoice_items.fields.total') }}</th>
                                            <th class="p-3 text-center font-bold text-[10px] tracking-tight w-16">{{ t('isp.billing.customers_manager.invoice_items.fields.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border/60">
                                        <tr v-for="(item, index) in form.invoice_items" :key="index" class="hover:bg-muted/5 transition-colors">
                                            <td class="p-2">
                                                <Input v-model="item.name" class="h-9 rounded-lg border-border/40" :placeholder="t('isp.billing.customers_manager.invoice_items.placeholders.item_name')" />
                                            </td>
                                            <td class="p-2">
                                                <Input type="number" v-model="item.price" class="h-9 text-right rounded-lg border-border/40" />
                                            </td>
                                            <td class="p-2">
                                                <Input type="number" v-model="item.qty" class="h-9 text-center rounded-lg border-border/40" min="1" />
                                            </td>
                                            <td class="p-2 text-right font-mono font-bold text-primary">
                                                {{ formatCurrency(item.price * item.qty) }}
                                            </td>
                                            <td class="p-2 text-center">
                                                <Button type="button" variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:bg-destructive/10 rounded-lg" @click="removeInvoiceItem(index)">
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </td>
                                        </tr>
                                        <tr v-if="form.invoice_items.length === 0">
                                            <td colspan="5" class="py-10 text-center text-muted-foreground/60 italic text-xs font-medium bg-muted/5">
                                                {{ t('isp.billing.customers_manager.messages.invoice_feature_soon') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-muted/20 font-bold" v-if="form.invoice_items.length > 0">
                                        <tr>
                                            <td colspan="3" class="p-4 text-right text-muted-foreground tracking-tight text-[10px]">{{ t('isp.billing.customers_manager.labels.grand_total') }}</td>
                                            <td class="p-4 text-right text-base tracking-tighter text-primary">{{ formatCurrency(invoiceItemsTotal) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                         </div>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, nextTick, shallowRef, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { 
    Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    Textarea, Accordion, AccordionContent, AccordionItem, AccordionTrigger, Checkbox, Card
} from '@/components/ui';
import ChevronLeft from 'lucide-vue-next/dist/esm/icons/chevron-left.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import EyeOff from 'lucide-vue-next/dist/esm/icons/eye-off.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import Gauge from 'lucide-vue-next/dist/esm/icons/gauge.js';
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';
import User from 'lucide-vue-next/dist/esm/icons/user.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import CreditCard from 'lucide-vue-next/dist/esm/icons/credit-card.js';
import ReceiptText from 'lucide-vue-next/dist/esm/icons/receipt-text.js';
import Calendar from 'lucide-vue-next/dist/esm/icons/calendar.js';
import TrendingUp from 'lucide-vue-next/dist/esm/icons/trending-up.js';
import type { IspPlan, IspUser, Customer } from '@/types/isp';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { formatCurrency } from '@/utils/format';

const props = defineProps<{
    customer: IspUser | null; 
    loading: boolean;
    title: string;
}>();

const emit = defineEmits(['save', 'cancel']);
const { t } = useI18n();

const isEdit = computed(() => !!props.customer);
const activeAccordion = ref('identity');
const showMikrotikPass = ref(false);
const plans = ref<IspPlan[]>([]);

interface DropdownItem {
    id: number;
    name: string;
    ip_address?: string;
}

const partners = shallowRef<DropdownItem[]>([]);
const routers = shallowRef<DropdownItem[]>([]);
const servers = shallowRef<DropdownItem[]>([]);
const fetchingResource = ref(false);
const fetchingLocation = ref(false);
const isGeocoding = ref(false);
const updatingFromMap = ref(false);
let geocodeTimeout: any = null;

// Regional Data State
interface Region { id: string; name: string; }
const provinces = ref<Region[]>([]);
const regencies = ref<Region[]>([]);
const districts = ref<Region[]>([]);
const villages = ref<Region[]>([]);

const selectedProvinceId = ref('');
const selectedRegencyId = ref('');
const selectedDistrictId = ref('');
const selectedVillageId = ref('');

const fetchProvinces = async () => {
    try {
        const res = await fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
        provinces.value = await res.json();
    } catch (e) { console.error('Failed to fetch provinces', e); }
};

const fetchRegencies = async (provinceId: string) => {
    if (!provinceId) return;
    try {
        const res = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`);
        regencies.value = await res.json();
    } catch (e) { console.error('Failed to fetch regencies', e); }
};

const fetchDistricts = async (regencyId: string) => {
    if (!regencyId) return;
    try {
        const res = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${regencyId}.json`);
        districts.value = await res.json();
    } catch (e) { console.error('Failed to fetch districts', e); }
};

const fetchVillages = async (districtId: string) => {
    if (!districtId) return;
    try {
        const res = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${districtId}.json`);
        villages.value = await res.json();
    } catch (e) { console.error('Failed to fetch villages', e); }
};

// Map state
const mapContainer = ref<HTMLElement | null>(null);
let mapInstance: L.Map | null = null;
let markerInstance: L.Marker | null = null;

const reverseGeocode = async (lat: number, lng: number) => {
    isGeocoding.value = true;
    updatingFromMap.value = true;
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18&countrycodes=id&accept-language=id`, {
            headers: { 'User-Agent': 'JA-KDUA-ISP-Manager' }
        });
        const data = await response.json();
        if (data.address) {
            const addr = data.address;
            form.value.address_street = [addr.road, addr.house_number].filter(Boolean).join(' ') || form.value.address_street;
            
            // Try to match with regional dropdowns
            if (addr.state) {
                const p = provinces.value.find(x => x.name.toLowerCase().includes(addr.state.toLowerCase()));
                if (p) {
                    selectedProvinceId.value = p.id;
                    await fetchRegencies(p.id);
                    const city = addr.city || addr.regency || addr.municipality;
                    if (city) {
                        const r = regencies.value.find(x => x.name.toLowerCase().includes(city.toLowerCase()));
                        if (r) {
                            selectedRegencyId.value = r.id;
                            await fetchDistricts(r.id);
                            const district = addr.city_district || addr.district;
                            if (district) {
                                const d = districts.value.find(x => x.name.toLowerCase().includes(district.toLowerCase()));
                                if (d) {
                                    selectedDistrictId.value = d.id;
                                    await fetchVillages(d.id);
                                    const village = addr.village || addr.suburb || addr.neighbourhood;
                                    if (village) {
                                        const v = villages.value.find(x => x.name.toLowerCase().includes(village.toLowerCase()));
                                        if (v) selectedVillageId.value = v.id;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            form.value.address_postal_code = addr.postcode || '';
        }
    } catch (e) {
        console.error('Reverse geocoding failed', e);
    } finally {
        isGeocoding.value = false;
        // Release the lock after a short delay to ensure watchers have seen the changes
        setTimeout(() => {
            updatingFromMap.value = false;
        }, 500);
    }
};

const forwardGeocode = async () => {
    if (updatingFromMap.value) return;

    const address = [
        form.value.address_street,
        form.value.address_village,
        form.value.address_district,
        form.value.address_city,
        form.value.address_province
    ].filter(Boolean).join(', ');

    if (address.length < 5) return;

    isGeocoding.value = true;
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(address)}&countrycodes=id&limit=1&accept-language=id`, {
            headers: { 'User-Agent': 'JA-KDUA-ISP-Manager' }
        });
        const data = await response.json();
        if (data && data.length > 0) {
            const { lat, lon } = data[0];
            const latitude = parseFloat(lat);
            const longitude = parseFloat(lon);
            updateMarker(latitude, longitude, false);
            mapInstance?.setView([latitude, longitude], 16);
        }
    } catch (e) {
        console.error('Forward geocoding failed', e);
    } finally {
        isGeocoding.value = false;
    }
};

const debouncedForwardGeocode = () => {
    if (updatingFromMap.value) return;
    if (geocodeTimeout) clearTimeout(geocodeTimeout);
    geocodeTimeout = setTimeout(() => {
        if (!isGeocoding.value && !updatingFromMap.value) forwardGeocode();
    }, 1500);
};

// Cascading Handlers
watch(selectedProvinceId, async (newId) => {
    if (!newId) return;
    const found = provinces.value.find(p => p.id === newId);
    if (found) {
        form.value.address_province = found.name;
        await fetchRegencies(newId);
        if (!updatingFromMap.value) debouncedForwardGeocode();
    }
});

watch(selectedRegencyId, async (newId) => {
    if (!newId) return;
    const found = regencies.value.find(r => r.id === newId);
    if (found) {
        form.value.address_city = found.name;
        await fetchDistricts(newId);
        if (!updatingFromMap.value) debouncedForwardGeocode();
    }
});

watch(selectedDistrictId, async (newId) => {
    if (!newId) return;
    const found = districts.value.find(d => d.id === newId);
    if (found) {
        form.value.address_district = found.name;
        await fetchVillages(newId);
        if (!updatingFromMap.value) debouncedForwardGeocode();
    }
});

watch(selectedVillageId, (newId) => {
    if (!newId) return;
    const found = villages.value.find(v => v.id === newId);
    if (found) {
        form.value.address_village = found.name;
        if (!updatingFromMap.value) debouncedForwardGeocode();
    }
});

const getCurrentLocation = () => {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }

    fetchingLocation.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            updateMarker(latitude, longitude);
            mapInstance?.setView([latitude, longitude], 17);
            fetchingLocation.value = false;
        },
        (error) => {
            console.error('Geolocation error:', error);
            fetchingLocation.value = false;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
};

// Form state
const form = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    identity_type: 'KTP',
    identity_number: '',
    address_street: '',
    address_village: '',
    address_district: '',
    address_city: '',
    address_province: '',
    address_postal_code: '',
    coordinates: '',
    billing_plan_id_str: '',
    billing_cycle_start: 1,
    installation_date: '',
    status: 'active',
    mikrotik_login: '',
    mikrotik_password: '',
    partner_id_str: '',
    router_id_str: '',
    server_id_str: '',
    address_list: '',
    service_category: '',
    billing_due_date: '',
    billing_notes: '',
    is_taxed: false,
    unique_code: 0,
    invoice_items: [] as { name: string, price: number, qty: number }[]
});

const addInvoiceItem = () => {
    form.value.invoice_items.push({ name: '', price: 0, qty: 1 });
};

const removeInvoiceItem = (index: number) => {
    form.value.invoice_items.splice(index, 1);
};

const invoiceItemsTotal = computed(() => {
    return form.value.invoice_items.reduce((sum, item) => sum + (item.price * item.qty), 0);
});

const formatBytes = (bytes: number) => {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const usagePercentage = computed(() => {
    const c = props.customer;
    if (!c) return 0;
    // Attempt to find the profile in customer relation, legacy isp_customer, or the object itself
    const profile = (c.customer || c.isp_customer || c) as Customer;
    if (!profile.billing_plan_id) return 0;
    
    const plan = plans.value.find(p => p.id === Number(profile.billing_plan_id));
    if (!plan || !plan.fup_enabled || !plan.fup_limit_gb) return 0;
    
    const usageGb = (profile.current_usage_bytes || 0) / (1024 * 1024 * 1024);
    return Math.min(Math.round((usageGb / plan.fup_limit_gb) * 100), 100);
});

const cycleEndDateLabel = computed(() => {
    const c = props.customer;
    if (!c) return 'N/A';
    const profile = (c.customer || c.isp_customer || c) as Customer;
    if (!profile?.last_usage_reset_at) return 'N/A';
    
    try {
        return new Date(profile.last_usage_reset_at).toLocaleDateString();
    } catch (e) {
        return 'N/A';
    }
});

const fetchResources = async () => {
    if (fetchingResource.value) return;
    fetchingResource.value = true;
    try {
        const params = { per_page: 100 };
        const [planRes, partnerRes, routerRes, serverRes] = await Promise.all([
            api.get('/admin/janet/isp/billing-plans', { params }),
            api.get('/admin/janet/isp/partners', { params }),
            api.get('/admin/janet/isp/routers', { params }),
            api.get('/admin/janet/isp/service-zones', { params })
        ]);

        const extractData = (res: any) => {
            const payload = res.data;
            if (payload.data && payload.data.data && Array.isArray(payload.data.data)) return payload.data.data;
            if (payload.data && Array.isArray(payload.data)) return payload.data;
            if (Array.isArray(payload)) return payload;
            return [];
        };

        plans.value = extractData(planRes);
        partners.value = extractData(partnerRes);
        routers.value = extractData(routerRes);
        servers.value = extractData(serverRes);
    } catch (e) {
        console.error('Failed to load resources', e);
    } finally {
        fetchingResource.value = false;
    }
};

const initMap = () => {
    if (!mapContainer.value) return;

    // Check if mapInstance is already initialized on the current container
    if (mapInstance) {
        try {
            const container = mapInstance.getContainer();
            if (document.body.contains(container) && (container === mapContainer.value)) {
                mapInstance.invalidateSize();
                return;
            }
        } catch (e) {
            // Ignore error
        }
        // If container is stale or something is wrong, cleanup
        mapInstance.remove();
        mapInstance = null;
    }

    if (!mapContainer.value) return;

    mapInstance = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false
    }).setView([-6.2088, 106.8456], 13);

    // Leaflet Icon Fix for Vite
    delete (L.Icon.Default.prototype as any)._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapInstance as L.Map);

    mapInstance.on('click', (e: any) => {
        const { lat, lng } = e.latlng;
        updateMarker(lat, lng);
    });

    if (form.value.coordinates) {
        const coords = form.value.coordinates.split(',');
        if (coords.length === 2) {
            const cLat = parseFloat(coords[0].trim());
            const cLng = parseFloat(coords[1].trim());
            if (!isNaN(cLat) && !isNaN(cLng)) {
                updateMarker(cLat, cLng);
                mapInstance.setView([cLat, cLng], 15);
            }
        }
    }
};

const updateMarker = (lat: number, lng: number, triggerReverse = false) => {
    if (!mapInstance) return;
    
    if (markerInstance) {
        markerInstance.setLatLng([lat, lng]);
    } else {
        markerInstance = L.marker([lat, lng]).addTo(mapInstance);
    }
    form.value.coordinates = `${lat}, ${lng}`;

    if (triggerReverse && !isGeocoding.value) {
        reverseGeocode(lat, lng);
    }
};

watch(() => form.value.address_street, debouncedForwardGeocode);
watch(() => form.value.address_village, debouncedForwardGeocode);
watch(() => form.value.address_district, debouncedForwardGeocode);
watch(() => form.value.address_city, debouncedForwardGeocode);
watch(() => form.value.address_province, debouncedForwardGeocode);

watch(activeAccordion, (section) => {
    if (section === 'address') {
        // Wait for accordion transition to complete (approx 300-500ms)
        setTimeout(() => {
            initMap();
            // Force map to recalculate its container dimensions
            if (mapInstance) {
                mapInstance.invalidateSize();
                window.dispatchEvent(new Event('resize'));
            }
        }, 500);
    }
});
// Cleanup on unmounted is handled here.
onUnmounted(() => {
    if (mapInstance) {
        mapInstance.remove();
        mapInstance = null;
    }
});

onMounted(async () => {
    fetchResources();
    await fetchProvinces();
    
    if (props.customer) {
        const c = props.customer;
        // Fallback to c if relations are missing
        const profile = (c.customer || c.isp_customer || c) as Customer;
        
        form.value = {
            name: c.name,
            email: c.email,
            phone: c.phone || '',
            password: '',
            identity_type: profile.identity_type || 'KTP',
            identity_number: profile.identity_number || '',
            address_street: profile.address_street || '',
            address_village: profile.address_village || '',
            address_district: profile.address_district || '',
            address_city: profile.address_city || '',
            address_province: profile.address_province || '',
            address_postal_code: profile.address_postal_code || '',
            coordinates: profile.coordinates || '',
            billing_plan_id_str: profile.billing_plan_id ? String(profile.billing_plan_id) : '',
            billing_cycle_start: profile.billing_cycle_start || 1,
            installation_date: profile.installation_date || '',
            status: profile.status || 'active',
            mikrotik_login: profile.mikrotik_login || '',
            mikrotik_password: profile.mikrotik_password || '',
            partner_id_str: profile.partner_id ? String(profile.partner_id) : '',
            router_id_str: profile.router_id ? String(profile.router_id) : '',
            server_id_str: profile.server_id ? String(profile.server_id) : '',
            address_list: profile.address_list || '',
            service_category: profile.service_category || '',
            billing_due_date: profile.billing_due_date ? String(profile.billing_due_date) : '',
            billing_notes: profile.billing_notes || '',
            is_taxed: !!profile.is_taxed,
            unique_code: profile.unique_code || 0,
            invoice_items: []
        };

        // Initialize regional selects from names
        if (form.value.address_province) {
            const p = provinces.value.find(x => x.name.toLowerCase() === form.value.address_province.toLowerCase());
            if (p) {
                selectedProvinceId.value = p.id;
                await fetchRegencies(p.id);
                if (form.value.address_city) {
                    const r = regencies.value.find(x => x.name.toLowerCase() === form.value.address_city.toLowerCase());
                    if (r) {
                        selectedRegencyId.value = r.id;
                        await fetchDistricts(r.id);
                        if (form.value.address_district) {
                            const d = districts.value.find(x => x.name.toLowerCase() === form.value.address_district.toLowerCase());
                            if (d) {
                                selectedDistrictId.value = d.id;
                                await fetchVillages(d.id);
                                if (form.value.address_village) {
                                    const v = villages.value.find(x => x.name.toLowerCase() === form.value.address_village.toLowerCase());
                                    if (v) selectedVillageId.value = v.id;
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
         form.value = {
            name: '',
            email: '',
            phone: '',
            password: '',
            identity_type: 'KTP',
            identity_number: '',
            address_street: '',
            address_village: '',
            address_district: '',
            address_city: '',
            address_province: '',
            address_postal_code: '',
            coordinates: '',
            billing_plan_id_str: '',
            billing_cycle_start: 1,
            installation_date: new Date().toISOString().split('T')[0],
            status: 'active',
            mikrotik_login: '',
            mikrotik_password: '',
            partner_id_str: '',
            router_id_str: '',
            server_id_str: '',
            address_list: '',
            service_category: '',
            billing_due_date: '',
            billing_notes: '',
            is_taxed: false,
            unique_code: Math.floor(Math.random() * 900) + 100,
            invoice_items: []
        };
    }
});

const save = () => {
    emit('save', {
        ...form.value,
        billing_plan_id: Number(form.value.billing_plan_id_str),
        partner_id: form.value.partner_id_str ? Number(form.value.partner_id_str) : null,
        router_id: form.value.router_id_str ? Number(form.value.router_id_str) : null,
        server_id: form.value.server_id_str ? Number(form.value.server_id_str) : null,
        is_taxed: form.value.is_taxed ? 1 : 0
    });
};
</script>
