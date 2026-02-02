import { defineAsyncComponent } from 'vue'
import ModuleRegistry from './ModuleRegistry'

/**
 * Block Components Registration
 * Dynamically registers all block Vue components to optimize bundle size
 */
export function registerBlockComponents() {
    // Structure
    ModuleRegistry.registerComponent('section', defineAsyncComponent(() => import('@/shared/blocks/SectionBlock.vue')))
    ModuleRegistry.registerComponent('row', defineAsyncComponent(() => import('@/shared/blocks/RowBlock.vue')))
    ModuleRegistry.registerComponent('column', defineAsyncComponent(() => import('@/shared/blocks/ColumnBlock.vue')))

    // Basic
    ModuleRegistry.registerComponent('heading', defineAsyncComponent(() => import('@/shared/blocks/HeadingBlock.vue')))
    ModuleRegistry.registerComponent('richtext', defineAsyncComponent(() => import('@/shared/blocks/RichTextBlock.vue')))
    ModuleRegistry.registerComponent('text', defineAsyncComponent(() => import('@/shared/blocks/TextBlock.vue')))
    ModuleRegistry.registerComponent('button', defineAsyncComponent(() => import('@/shared/blocks/ButtonBlock.vue')))
    ModuleRegistry.registerComponent('image', defineAsyncComponent(() => import('@/shared/blocks/ImageBlock.vue')))
    ModuleRegistry.registerComponent('icon', defineAsyncComponent(() => import('@/shared/blocks/IconBlock.vue')))
    ModuleRegistry.registerComponent('divider', defineAsyncComponent(() => import('@/shared/blocks/DividerBlock.vue')))
    ModuleRegistry.registerComponent('spacer', defineAsyncComponent(() => import('@/shared/blocks/SpacerBlock.vue')))
    ModuleRegistry.registerComponent('logo', defineAsyncComponent(() => import('@/shared/blocks/LogoBlock.vue')))

    // Media
    ModuleRegistry.registerComponent('video', defineAsyncComponent(() => import('@/shared/blocks/VideoBlock.vue')))
    ModuleRegistry.registerComponent('gallery', defineAsyncComponent(() => import('@/shared/blocks/GalleryBlock.vue')))
    ModuleRegistry.registerComponent('map', defineAsyncComponent(() => import('@/shared/blocks/MapBlock.vue')))
    ModuleRegistry.registerComponent('map_pin', defineAsyncComponent(() => import('@/shared/blocks/MapPinBlock.vue')))
    ModuleRegistry.registerComponent('audio', defineAsyncComponent(() => import('@/shared/blocks/AudioBlock.vue')))
    ModuleRegistry.registerComponent('beforeafter', defineAsyncComponent(() => import('@/shared/blocks/BeforeAfterBlock.vue')))
    ModuleRegistry.registerComponent('videopopup', defineAsyncComponent(() => import('@/shared/blocks/VideoPopupBlock.vue')))
    ModuleRegistry.registerComponent('embed', defineAsyncComponent(() => import('@/shared/blocks/EmbedBlock.vue')))
    ModuleRegistry.registerComponent('lottie', defineAsyncComponent(() => import('@/shared/blocks/LottieBlock.vue')))
    ModuleRegistry.registerComponent('videoslider', defineAsyncComponent(() => import('@/shared/blocks/VideoSliderBlock.vue')))

    // Content
    ModuleRegistry.registerComponent('blurb', defineAsyncComponent(() => import('@/shared/blocks/BlurbBlock.vue')))
    ModuleRegistry.registerComponent('cta', defineAsyncComponent(() => import('@/shared/blocks/CTABlock.vue')))
    ModuleRegistry.registerComponent('counter', defineAsyncComponent(() => import('@/shared/blocks/CounterBlock.vue')))
    ModuleRegistry.registerComponent('testimonial', defineAsyncComponent(() => import('@/shared/blocks/TestimonialBlock.vue')))
    ModuleRegistry.registerComponent('sociallinks', defineAsyncComponent(() => import('@/shared/blocks/SocialLinksBlock.vue')))
    ModuleRegistry.registerComponent('countdown', defineAsyncComponent(() => import('@/shared/blocks/CountdownBlock.vue')))
    ModuleRegistry.registerComponent('progressbar', defineAsyncComponent(() => import('@/shared/blocks/ProgressBarBlock.vue')))
    ModuleRegistry.registerComponent('pricingtable', defineAsyncComponent(() => import('@/shared/blocks/PricingBlock.vue')))
    ModuleRegistry.registerComponent('pricing_feature', defineAsyncComponent(() => import('@/shared/blocks/PricingFeatureBlock.vue')))
    ModuleRegistry.registerComponent('alert', defineAsyncComponent(() => import('@/shared/blocks/AlertBlock.vue')))
    ModuleRegistry.registerComponent('code', defineAsyncComponent(() => import('@/shared/blocks/CodeBlock.vue')))
    ModuleRegistry.registerComponent('teammember', defineAsyncComponent(() => import('@/shared/blocks/TeamMemberBlock.vue')))
    ModuleRegistry.registerComponent('circlecounter', defineAsyncComponent(() => import('@/shared/blocks/CircleCounterBlock.vue')))
    ModuleRegistry.registerComponent('iconlist', defineAsyncComponent(() => import('@/shared/blocks/IconListBlock.vue')))
    ModuleRegistry.registerComponent('breadcrumbs', defineAsyncComponent(() => import('@/shared/blocks/BreadcrumbsBlock.vue')))
    ModuleRegistry.registerComponent('author', defineAsyncComponent(() => import('@/shared/blocks/AuthorBlock.vue')))
    ModuleRegistry.registerComponent('starrating', defineAsyncComponent(() => import('@/shared/blocks/StarRatingBlock.vue')))
    ModuleRegistry.registerComponent('tableofcontents', defineAsyncComponent(() => import('@/shared/blocks/TableOfContentsBlock.vue')))
    ModuleRegistry.registerComponent('quote', defineAsyncComponent(() => import('@/shared/blocks/QuoteBlock.vue')))
    ModuleRegistry.registerComponent('logogrid', defineAsyncComponent(() => import('@/shared/blocks/LogoGridBlock.vue')))
    ModuleRegistry.registerComponent('faq', defineAsyncComponent(() => import('@/shared/blocks/FAQBlock.vue')))
    ModuleRegistry.registerComponent('feature', defineAsyncComponent(() => import('@/shared/blocks/FeatureBlock.vue')))
    ModuleRegistry.registerComponent('hero', defineAsyncComponent(() => import('@/shared/blocks/HeroBlock.vue')))
    ModuleRegistry.registerComponent('numberbox', defineAsyncComponent(() => import('@/shared/blocks/NumberBoxBlock.vue')))
    ModuleRegistry.registerComponent('sharebuttons', defineAsyncComponent(() => import('@/shared/blocks/ShareButtonsBlock.vue')))
    ModuleRegistry.registerComponent('sidebar', defineAsyncComponent(() => import('@/shared/blocks/SidebarBlock.vue')))
    ModuleRegistry.registerComponent('menu', defineAsyncComponent(() => import('@/shared/blocks/MenuBlock.vue')))
    ModuleRegistry.registerComponent('numbercounter', defineAsyncComponent(() => import('@/shared/blocks/NumberCounterBlock.vue')))
    ModuleRegistry.registerComponent('group', defineAsyncComponent(() => import('@/shared/blocks/GroupBlock.vue')))
    ModuleRegistry.registerComponent('groupcarousel', defineAsyncComponent(() => import('@/shared/blocks/GroupCarouselBlock.vue')))

    // Interactive
    ModuleRegistry.registerComponent('tabs', defineAsyncComponent(() => import('@/shared/blocks/TabsBlock.vue')))
    ModuleRegistry.registerComponent('accordion', defineAsyncComponent(() => import('@/shared/blocks/AccordionBlock.vue')))
    ModuleRegistry.registerComponent('toggle', defineAsyncComponent(() => import('@/shared/blocks/ToggleBlock.vue')))
    ModuleRegistry.registerComponent('slider', defineAsyncComponent(() => import('@/shared/blocks/SliderBlock.vue')))

    // Forms
    ModuleRegistry.registerComponent('search', defineAsyncComponent(() => import('@/shared/blocks/SearchBlock.vue')))
    ModuleRegistry.registerComponent('login', defineAsyncComponent(() => import('@/shared/blocks/LoginBlock.vue')))
    ModuleRegistry.registerComponent('contactform', defineAsyncComponent(() => import('@/shared/blocks/ContactFormBlock.vue')))
    ModuleRegistry.registerComponent('signup', defineAsyncComponent(() => import('@/shared/blocks/SignupBlock.vue')))
    ModuleRegistry.registerComponent('newsletter', defineAsyncComponent(() => import('@/shared/blocks/NewsletterBlock.vue')))

    // Form Builder Fields
    ModuleRegistry.registerComponent('form_input', defineAsyncComponent(() => import('@/shared/blocks/FormInputBlock.vue')))
    ModuleRegistry.registerComponent('form_textarea', defineAsyncComponent(() => import('@/shared/blocks/FormTextareaBlock.vue')))
    ModuleRegistry.registerComponent('form_select', defineAsyncComponent(() => import('@/shared/blocks/FormSelectBlock.vue')))
    ModuleRegistry.registerComponent('form_checkbox', defineAsyncComponent(() => import('@/shared/blocks/FormCheckboxBlock.vue')))
    ModuleRegistry.registerComponent('form_radio', defineAsyncComponent(() => import('@/shared/blocks/FormRadioBlock.vue')))

    // Dynamic
    ModuleRegistry.registerComponent('blog', defineAsyncComponent(() => import('@/shared/blocks/BlogBlock.vue')))
    ModuleRegistry.registerComponent('portfolio', defineAsyncComponent(() => import('@/shared/blocks/PortfolioBlock.vue')))
    ModuleRegistry.registerComponent('postslider', defineAsyncComponent(() => import('@/shared/blocks/PostSliderBlock.vue')))
    ModuleRegistry.registerComponent('comments', defineAsyncComponent(() => import('@/shared/blocks/CommentsBlock.vue')))
    ModuleRegistry.registerComponent('postnavigation', defineAsyncComponent(() => import('@/shared/blocks/PostNavigationBlock.vue')))
    ModuleRegistry.registerComponent('relatedposts', defineAsyncComponent(() => import('@/shared/blocks/RelatedPostsBlock.vue')))
    ModuleRegistry.registerComponent('posttitle', defineAsyncComponent(() => import('@/shared/blocks/PostTitleBlock.vue')))
    ModuleRegistry.registerComponent('postmeta', defineAsyncComponent(() => import('@/shared/blocks/PostMetaBlock.vue')))
    ModuleRegistry.registerComponent('featuredimage', defineAsyncComponent(() => import('@/shared/blocks/FeaturedImageBlock.vue')))
    ModuleRegistry.registerComponent('postcontent', defineAsyncComponent(() => import('@/shared/blocks/PostContentBlock.vue')))
    ModuleRegistry.registerComponent('filterableportfolio', defineAsyncComponent(() => import('@/shared/blocks/FilterablePortfolioBlock.vue')))
    ModuleRegistry.registerComponent('tabbedposts', defineAsyncComponent(() => import('@/shared/blocks/TabbedPostsBlock.vue')))

    // Fullwidth
    ModuleRegistry.registerComponent('fullwidthheader', defineAsyncComponent(() => import('@/shared/blocks/FullwidthHeaderBlock.vue')))
    ModuleRegistry.registerComponent('fullwidthslider', defineAsyncComponent(() => import('@/shared/blocks/FullwidthSliderBlock.vue')))
    ModuleRegistry.registerComponent('fullwidth_slide_item', defineAsyncComponent(() => import('@/shared/blocks/FullwidthSlideItemBlock.vue')))
    ModuleRegistry.registerComponent('fullwidthportfolio', defineAsyncComponent(() => import('@/shared/blocks/FullwidthPortfolioBlock.vue')))
    ModuleRegistry.registerComponent('fullwidthcode', defineAsyncComponent(() => import('@/shared/blocks/FullwidthCodeBlock.vue')))
    ModuleRegistry.registerComponent('fullwidthimage', defineAsyncComponent(() => import('@/shared/blocks/FullwidthImageBlock.vue')))
    ModuleRegistry.registerComponent('fullwidthmap', defineAsyncComponent(() => import('@/shared/blocks/FullwidthMapBlock.vue')))
    ModuleRegistry.registerComponent('fullwidthmenu', defineAsyncComponent(() => import('@/shared/blocks/FullwidthMenuBlock.vue')))
    ModuleRegistry.registerComponent('fullwidthpostcontent', defineAsyncComponent(() => import('@/shared/blocks/FullwidthPostContentBlock.vue')))
    ModuleRegistry.registerComponent('fullwidthpostslider', defineAsyncComponent(() => import('@/shared/blocks/FullwidthPostSliderBlock.vue')))
    ModuleRegistry.registerComponent('fullwidthposttitle', defineAsyncComponent(() => import('@/shared/blocks/FullwidthPostTitleBlock.vue')))
}

export default registerBlockComponents
