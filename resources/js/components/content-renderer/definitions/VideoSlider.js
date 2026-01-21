export default {
    type: 'videoslider',
    name: 'videoslider',
    component: 'VideoSliderBlock',
    settings: [
        { name: 'items', type: 'repeater' },
        { name: 'showArrows', type: 'boolean' },
        { name: 'showDots', type: 'boolean' },
        { name: 'showThumbnails', type: 'boolean' },
        { name: 'thumbnailPosition', type: 'string' },
        { name: 'autoplay', type: 'boolean' },
        { name: 'autoplaySpeed', type: 'number' },
        { name: 'aspectRatio', type: 'string' },
        { name: 'slidesPerView', type: 'number' },
        { name: 'gap', type: 'number' },
        { name: 'videoAutoplay', type: 'boolean' },
        { name: 'videoMuted', type: 'boolean' },
        { name: 'videoLoop', type: 'boolean' },
        { name: 'showControls', type: 'boolean' },
        { name: 'showPlayButton', type: 'boolean' },
        { name: 'playButtonSize', type: 'number' },
        { name: 'playButtonColor', type: 'string' },
        { name: 'overlayColor', type: 'string' }
    ]
};
