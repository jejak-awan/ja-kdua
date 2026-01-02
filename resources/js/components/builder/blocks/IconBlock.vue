<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['flex', alignmentClass]">
            <div 
                :class="[
                    'flex items-center justify-center transition-all',
                    sizeClass,
                    shapeClass
                ]"
                :style="{ 
                    backgroundColor: iconBgColor || 'transparent',
                    color: iconColor || 'currentColor'
                }"
            >
                <component :is="iconComponent" :class="iconSizeClass" />
            </div>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed } from 'vue';
import { 
    Star, Heart, Check, X, Plus, Minus, 
    ArrowRight, ArrowLeft, ArrowUp, ArrowDown,
    Mail, Phone, MapPin, Globe, Calendar, Clock,
    User, Users, Settings, Home, Search, Menu,
    Bell, Bookmark, Camera, Download, Upload, Share,
    Eye, Edit, Trash, Copy, Link, ExternalLink,
    Play, Pause, Volume2, Mic, Image, Video,
    File, Folder, Cloud, Database, Server, Code,
    Shield, Lock, Key, Award, Trophy, Zap,
    Sun, Moon, Sparkles, Flame, Droplet, Wind
} from 'lucide-vue-next';

const props = defineProps({
    icon: { type: String, default: 'star' },
    size: { type: String, default: 'medium' },
    shape: { type: String, default: 'none' },
    alignment: { type: String, default: 'center' },
    iconColor: { type: String, default: '' },
    iconBgColor: { type: String, default: '' },
    padding: { type: String, default: 'py-8' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const icons = {
    star: Star, heart: Heart, check: Check, x: X, plus: Plus, minus: Minus,
    'arrow-right': ArrowRight, 'arrow-left': ArrowLeft, 'arrow-up': ArrowUp, 'arrow-down': ArrowDown,
    mail: Mail, phone: Phone, 'map-pin': MapPin, globe: Globe, calendar: Calendar, clock: Clock,
    user: User, users: Users, settings: Settings, home: Home, search: Search, menu: Menu,
    bell: Bell, bookmark: Bookmark, camera: Camera, download: Download, upload: Upload, share: Share,
    eye: Eye, edit: Edit, trash: Trash, copy: Copy, link: Link, 'external-link': ExternalLink,
    play: Play, pause: Pause, volume: Volume2, mic: Mic, image: Image, video: Video,
    file: File, folder: Folder, cloud: Cloud, database: Database, server: Server, code: Code,
    shield: Shield, lock: Lock, key: Key, award: Award, trophy: Trophy, zap: Zap,
    sun: Sun, moon: Moon, sparkles: Sparkles, flame: Flame, droplet: Droplet, wind: Wind
};

const iconComponent = computed(() => icons[props.icon] || Star);

const alignmentClass = computed(() => ({
    'left': 'justify-start',
    'center': 'justify-center',
    'right': 'justify-end'
}[props.alignment] || 'justify-center'));

const sizeClass = computed(() => ({
    'small': 'w-12 h-12',
    'medium': 'w-16 h-16',
    'large': 'w-24 h-24',
    'xlarge': 'w-32 h-32'
}[props.size] || 'w-16 h-16'));

const iconSizeClass = computed(() => ({
    'small': 'w-6 h-6',
    'medium': 'w-8 h-8',
    'large': 'w-12 h-12',
    'xlarge': 'w-16 h-16'
}[props.size] || 'w-8 h-8'));

const shapeClass = computed(() => ({
    'none': '',
    'circle': 'rounded-full',
    'rounded': 'rounded-2xl',
    'square': 'rounded-none'
}[props.shape] || ''));
</script>
