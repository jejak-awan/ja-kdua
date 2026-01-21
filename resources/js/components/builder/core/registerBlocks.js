/**
 * Block Components Registration
 * Imports and registers all block Vue components
 */

import ModuleRegistry from './ModuleRegistry'

// Structure Blocks
import SectionBlock from '../blocks/SectionBlock.vue'
import RowBlock from '../blocks/RowBlock.vue'
import ColumnBlock from '../blocks/ColumnBlock.vue'

// Basic Blocks
import HeadingBlock from '../blocks/HeadingBlock.vue'
import TextBlock from '../blocks/TextBlock.vue'
import ButtonBlock from '../blocks/ButtonBlock.vue'
import ImageBlock from '../blocks/ImageBlock.vue'
import IconBlock from '../blocks/IconBlock.vue'
import DividerBlock from '../blocks/DividerBlock.vue'
import SpacerBlock from '../blocks/SpacerBlock.vue'
import LogoBlock from '../blocks/LogoBlock.vue'

// Media Blocks
import VideoBlock from '../blocks/VideoBlock.vue'
import GalleryBlock from '../blocks/GalleryBlock.vue'
// import GalleryItemBlock from '../blocks/GalleryItemBlock.vue' // REMOVED
import MapBlock from '../blocks/MapBlock.vue'
import MapPinBlock from '../blocks/MapPinBlock.vue'
import AudioBlock from '../blocks/AudioBlock.vue'
import BeforeAfterBlock from '../blocks/BeforeAfterBlock.vue'
import VideoPopupBlock from '../blocks/VideoPopupBlock.vue'
import EmbedBlock from '../blocks/EmbedBlock.vue'
import LottieBlock from '../blocks/LottieBlock.vue'
import VideoSliderBlock from '../blocks/VideoSliderBlock.vue'
// import VideoSlideItemBlock from '../blocks/VideoSlideItemBlock.vue' // REMOVED

// Content Blocks
import BlurbBlock from '../blocks/BlurbBlock.vue'
import CTABlock from '../blocks/CTABlock.vue'
import CounterBlock from '../blocks/CounterBlock.vue'
import TestimonialBlock from '../blocks/TestimonialBlock.vue'
import SocialLinksBlock from '../blocks/SocialLinksBlock.vue'
import CountdownBlock from '../blocks/CountdownBlock.vue'
import ProgressBarBlock from '../blocks/ProgressBarBlock.vue'
import PricingTableBlock from '../blocks/PricingTableBlock.vue'
import PricingFeatureBlock from '../blocks/PricingFeatureBlock.vue'
import AlertBlock from '../blocks/AlertBlock.vue'
import CodeBlock from '../blocks/CodeBlock.vue'
import TeamMemberBlock from '../blocks/TeamMemberBlock.vue'
import CircleCounterBlock from '../blocks/CircleCounterBlock.vue'
import IconListBlock from '../blocks/IconListBlock.vue'
// import IconListItemBlock from '../blocks/IconListItemBlock.vue' // REMOVED
import BreadcrumbsBlock from '../blocks/BreadcrumbsBlock.vue'
import AuthorBlock from '../blocks/AuthorBlock.vue'
import StarRatingBlock from '../blocks/StarRatingBlock.vue'
import TableOfContentsBlock from '../blocks/TableOfContentsBlock.vue'
import QuoteBlock from '../blocks/QuoteBlock.vue'
import LogoGridBlock from '../blocks/LogoGridBlock.vue'
import FAQBlock from '../blocks/FAQBlock.vue'
// import FAQItemBlock from '../blocks/FAQItemBlock.vue' // REMOVED
import FeatureBlock from '../blocks/FeatureBlock.vue'
import NumberBoxBlock from '../blocks/NumberBoxBlock.vue'
import ShareButtonsBlock from '../blocks/ShareButtonsBlock.vue'
import SidebarBlock from '../blocks/SidebarBlock.vue'
import MenuBlock from '../blocks/MenuBlock.vue'

import NumberCounterBlock from '../blocks/NumberCounterBlock.vue'
import GroupBlock from '../blocks/GroupBlock.vue'
import GroupCarouselBlock from '../blocks/GroupCarouselBlock.vue'

// Interactive Blocks
import TabsBlock from '../blocks/TabsBlock.vue'
// import TabItemBlock from '../blocks/TabItemBlock.vue' // REMOVED
import AccordionBlock from '../blocks/AccordionBlock.vue'
// import AccordionItemBlock from '../blocks/AccordionItemBlock.vue' // REMOVED
import ToggleBlock from '../blocks/ToggleBlock.vue'
import SliderBlock from '../blocks/SliderBlock.vue'
// import SlideItemBlock from '../blocks/SlideItemBlock.vue' // REMOVED

// Forms Blocks
import SearchBlock from '../blocks/SearchBlock.vue'
import LoginBlock from '../blocks/LoginBlock.vue'
import ContactFormBlock from '../blocks/ContactFormBlock.vue'
// import ContactFieldBlock from '../blocks/ContactFieldBlock.vue' // REMOVED
import SignupBlock from '../blocks/SignupBlock.vue'
import NewsletterBlock from '../blocks/NewsletterBlock.vue'

// Dynamic Blocks
import BlogBlock from '../blocks/BlogBlock.vue'
import PortfolioBlock from '../blocks/PortfolioBlock.vue'
import PostSliderBlock from '../blocks/PostSliderBlock.vue'
import CommentsBlock from '../blocks/CommentsBlock.vue'
import PostNavigationBlock from '../blocks/PostNavigationBlock.vue'
import RelatedPostsBlock from '../blocks/RelatedPostsBlock.vue'
import PostTitleBlock from '../blocks/PostTitleBlock.vue'
import PostMetaBlock from '../blocks/PostMetaBlock.vue'
import FeaturedImageBlock from '../blocks/FeaturedImageBlock.vue'
import PostContentBlock from '../blocks/PostContentBlock.vue'
import FilterablePortfolioBlock from '../blocks/FilterablePortfolioBlock.vue'

// Fullwidth Blocks
import FullwidthHeaderBlock from '../blocks/FullwidthHeaderBlock.vue'
import FullwidthSliderBlock from '../blocks/FullwidthSliderBlock.vue'
import FullwidthPortfolioBlock from '../blocks/FullwidthPortfolioBlock.vue'
import FullwidthCodeBlock from '../blocks/FullwidthCodeBlock.vue'
import FullwidthImageBlock from '../blocks/FullwidthImageBlock.vue'
import FullwidthMapBlock from '../blocks/FullwidthMapBlock.vue'
import FullwidthMenuBlock from '../blocks/FullwidthMenuBlock.vue'
import FullwidthPostContentBlock from '../blocks/FullwidthPostContentBlock.vue'
import FullwidthPostSliderBlock from '../blocks/FullwidthPostSliderBlock.vue'
import FullwidthPostTitleBlock from '../blocks/FullwidthPostTitleBlock.vue'
import FullwidthSlideItemBlock from '../blocks/FullwidthSlideItemBlock.vue'
// import SidebarWidgetBlock from '../blocks/SidebarWidgetBlock.vue' // REMOVED
// import LogoGridItemBlock from '../blocks/LogoGridItemBlock.vue' // REMOVED

/**
 * Register all block components
 */
export function registerBlockComponents() {
    // Structure
    ModuleRegistry.registerComponent('section', SectionBlock)
    ModuleRegistry.registerComponent('row', RowBlock)
    ModuleRegistry.registerComponent('column', ColumnBlock)

    // Basic
    ModuleRegistry.registerComponent('heading', HeadingBlock)
    ModuleRegistry.registerComponent('text', TextBlock)
    ModuleRegistry.registerComponent('button', ButtonBlock)
    ModuleRegistry.registerComponent('image', ImageBlock)
    ModuleRegistry.registerComponent('icon', IconBlock)
    ModuleRegistry.registerComponent('divider', DividerBlock)
    ModuleRegistry.registerComponent('spacer', SpacerBlock)
    ModuleRegistry.registerComponent('logo', LogoBlock)

    // Media
    ModuleRegistry.registerComponent('video', VideoBlock)
    ModuleRegistry.registerComponent('gallery', GalleryBlock)
    // ModuleRegistry.registerComponent('gallery_item', GalleryItemBlock) // REMOVED
    ModuleRegistry.registerComponent('map', MapBlock)
    ModuleRegistry.registerComponent('map_pin', MapPinBlock)
    ModuleRegistry.registerComponent('audio', AudioBlock)
    ModuleRegistry.registerComponent('beforeafter', BeforeAfterBlock)
    ModuleRegistry.registerComponent('videopopup', VideoPopupBlock)
    ModuleRegistry.registerComponent('embed', EmbedBlock)
    ModuleRegistry.registerComponent('lottie', LottieBlock)
    ModuleRegistry.registerComponent('videoslider', VideoSliderBlock)
    // ModuleRegistry.registerComponent('video_slide_item', VideoSlideItemBlock) // REMOVED

    // Content
    ModuleRegistry.registerComponent('blurb', BlurbBlock)
    ModuleRegistry.registerComponent('cta', CTABlock)
    ModuleRegistry.registerComponent('counter', CounterBlock)
    ModuleRegistry.registerComponent('testimonial', TestimonialBlock)
    ModuleRegistry.registerComponent('sociallinks', SocialLinksBlock)
    ModuleRegistry.registerComponent('countdown', CountdownBlock)
    ModuleRegistry.registerComponent('progressbar', ProgressBarBlock)
    ModuleRegistry.registerComponent('pricingtable', PricingTableBlock)
    ModuleRegistry.registerComponent('pricing_feature', PricingFeatureBlock)
    ModuleRegistry.registerComponent('alert', AlertBlock)
    ModuleRegistry.registerComponent('code', CodeBlock)
    ModuleRegistry.registerComponent('teammember', TeamMemberBlock)
    ModuleRegistry.registerComponent('circlecounter', CircleCounterBlock)
    ModuleRegistry.registerComponent('iconlist', IconListBlock)
    // ModuleRegistry.registerComponent('icon_list_item', IconListItemBlock) // REMOVED
    ModuleRegistry.registerComponent('breadcrumbs', BreadcrumbsBlock)
    ModuleRegistry.registerComponent('author', AuthorBlock)
    ModuleRegistry.registerComponent('starrating', StarRatingBlock)
    ModuleRegistry.registerComponent('tableofcontents', TableOfContentsBlock)
    ModuleRegistry.registerComponent('quote', QuoteBlock)
    ModuleRegistry.registerComponent('logogrid', LogoGridBlock)
    // ModuleRegistry.registerComponent('logo_grid_item', LogoGridItemBlock) // REMOVED
    ModuleRegistry.registerComponent('faq', FAQBlock)
    // ModuleRegistry.registerComponent('faq_item', FAQItemBlock) // REMOVED
    ModuleRegistry.registerComponent('feature', FeatureBlock)
    ModuleRegistry.registerComponent('numberbox', NumberBoxBlock)
    ModuleRegistry.registerComponent('sharebuttons', ShareButtonsBlock)
    ModuleRegistry.registerComponent('sidebar', SidebarBlock)
    // ModuleRegistry.registerComponent('sidebar_widget', SidebarWidgetBlock) // REMOVED
    ModuleRegistry.registerComponent('menu', MenuBlock)

    ModuleRegistry.registerComponent('numbercounter', NumberCounterBlock)
    ModuleRegistry.registerComponent('group', GroupBlock)
    ModuleRegistry.registerComponent('groupcarousel', GroupCarouselBlock)

    // Interactive
    ModuleRegistry.registerComponent('tabs', TabsBlock)
    // ModuleRegistry.registerComponent('tab_item', TabItemBlock) // REMOVED
    ModuleRegistry.registerComponent('accordion', AccordionBlock)
    // ModuleRegistry.registerComponent('accordion_item', AccordionItemBlock) // REMOVED
    ModuleRegistry.registerComponent('toggle', ToggleBlock)
    ModuleRegistry.registerComponent('slider', SliderBlock)
    // ModuleRegistry.registerComponent('slide_item', SlideItemBlock) // REMOVED

    // Forms
    ModuleRegistry.registerComponent('search', SearchBlock)
    ModuleRegistry.registerComponent('login', LoginBlock)
    ModuleRegistry.registerComponent('contactform', ContactFormBlock)
    // ModuleRegistry.registerComponent('contact_field', ContactFieldBlock) // REMOVED
    ModuleRegistry.registerComponent('signup', SignupBlock)
    ModuleRegistry.registerComponent('newsletter', NewsletterBlock)

    // Dynamic
    ModuleRegistry.registerComponent('blog', BlogBlock)
    ModuleRegistry.registerComponent('portfolio', PortfolioBlock)
    ModuleRegistry.registerComponent('postslider', PostSliderBlock)
    ModuleRegistry.registerComponent('comments', CommentsBlock)
    ModuleRegistry.registerComponent('postnavigation', PostNavigationBlock)
    ModuleRegistry.registerComponent('relatedposts', RelatedPostsBlock)
    ModuleRegistry.registerComponent('posttitle', PostTitleBlock)
    ModuleRegistry.registerComponent('postmeta', PostMetaBlock)
    ModuleRegistry.registerComponent('featuredimage', FeaturedImageBlock)
    ModuleRegistry.registerComponent('postcontent', PostContentBlock)
    ModuleRegistry.registerComponent('filterableportfolio', FilterablePortfolioBlock)

    // Fullwidth
    ModuleRegistry.registerComponent('fullwidthheader', FullwidthHeaderBlock)
    ModuleRegistry.registerComponent('fullwidthslider', FullwidthSliderBlock)
    ModuleRegistry.registerComponent('fullwidth_slide_item', FullwidthSlideItemBlock)
    ModuleRegistry.registerComponent('fullwidthportfolio', FullwidthPortfolioBlock)
    ModuleRegistry.registerComponent('fullwidthcode', FullwidthCodeBlock)
    ModuleRegistry.registerComponent('fullwidthimage', FullwidthImageBlock)
    ModuleRegistry.registerComponent('fullwidthmap', FullwidthMapBlock)
    ModuleRegistry.registerComponent('fullwidthmenu', FullwidthMenuBlock)
    ModuleRegistry.registerComponent('fullwidthpostcontent', FullwidthPostContentBlock)
    ModuleRegistry.registerComponent('fullwidthpostslider', FullwidthPostSliderBlock)
    ModuleRegistry.registerComponent('fullwidthposttitle', FullwidthPostTitleBlock)
}

export default registerBlockComponents
