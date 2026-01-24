/**
 * Block Components Registration
 * Imports and registers all block Vue components
 */

import ModuleRegistry from './ModuleRegistry'

// Structure Blocks
import SectionBlock from '../../../shared/blocks/SectionBlock.vue'
import RowBlock from '../../../shared/blocks/RowBlock.vue'
import ColumnBlock from '../../../shared/blocks/ColumnBlock.vue'

// Basic Blocks
import HeadingBlock from '../../../shared/blocks/HeadingBlock.vue'
import RichTextBlock from '../../../shared/blocks/RichTextBlock.vue'
import TextBlock from '../../../shared/blocks/TextBlock.vue'
import ButtonBlock from '../../../shared/blocks/ButtonBlock.vue'
import ImageBlock from '../../../shared/blocks/ImageBlock.vue'
import IconBlock from '../../../shared/blocks/IconBlock.vue'
import DividerBlock from '../../../shared/blocks/DividerBlock.vue'
import SpacerBlock from '../../../shared/blocks/SpacerBlock.vue'
import LogoBlock from '../../../shared/blocks/LogoBlock.vue'

// Media Blocks
import VideoBlock from '../../../shared/blocks/VideoBlock.vue'
import GalleryBlock from '../../../shared/blocks/GalleryBlock.vue'
import MapBlock from '../../../shared/blocks/MapBlock.vue'
import MapPinBlock from '../../../shared/blocks/MapPinBlock.vue'
import AudioBlock from '../../../shared/blocks/AudioBlock.vue'
import BeforeAfterBlock from '../../../shared/blocks/BeforeAfterBlock.vue'
import VideoPopupBlock from '../../../shared/blocks/VideoPopupBlock.vue'
import EmbedBlock from '../../../shared/blocks/EmbedBlock.vue'
import LottieBlock from '../../../shared/blocks/LottieBlock.vue'
import VideoSliderBlock from '../../../shared/blocks/VideoSliderBlock.vue'
// import VideoSlideItemBlock from '../blocks/VideoSlideItemBlock.vue' // REMOVED

// Content Blocks
import BlurbBlock from '../../../shared/blocks/BlurbBlock.vue'
import CTABlock from '../../../shared/blocks/CTABlock.vue'
import CounterBlock from '../../../shared/blocks/CounterBlock.vue'
import TestimonialBlock from '../../../shared/blocks/TestimonialBlock.vue'
import SocialLinksBlock from '../../../shared/blocks/SocialLinksBlock.vue'
import CountdownBlock from '../../../shared/blocks/CountdownBlock.vue'
import ProgressBarBlock from '../../../shared/blocks/ProgressBarBlock.vue'
import PricingTableBlock from '../../../shared/blocks/PricingBlock.vue'
import PricingFeatureBlock from '../../../shared/blocks/PricingFeatureBlock.vue'
import AlertBlock from '../../../shared/blocks/AlertBlock.vue'
import CodeBlock from '../../../shared/blocks/CodeBlock.vue'
import TeamMemberBlock from '../../../shared/blocks/TeamMemberBlock.vue'
import CircleCounterBlock from '../../../shared/blocks/CircleCounterBlock.vue'
import IconListBlock from '../../../shared/blocks/IconListBlock.vue'
// import IconListItemBlock from '../blocks/IconListItemBlock.vue' // REMOVED
import BreadcrumbsBlock from '../../../shared/blocks/BreadcrumbsBlock.vue'
import AuthorBlock from '../../../shared/blocks/AuthorBlock.vue'
import StarRatingBlock from '../../../shared/blocks/StarRatingBlock.vue'
import TableOfContentsBlock from '../../../shared/blocks/TableOfContentsBlock.vue'
import QuoteBlock from '../../../shared/blocks/QuoteBlock.vue'
import LogoGridBlock from '../../../shared/blocks/LogoGridBlock.vue'
import FAQBlock from '../../../shared/blocks/FAQBlock.vue'
// import FAQItemBlock from '../blocks/FAQItemBlock.vue' // REMOVED
import FeatureBlock from '../../../shared/blocks/FeatureBlock.vue'
import HeroBlock from '../../../shared/blocks/HeroBlock.vue'
import NumberBoxBlock from '../../../shared/blocks/NumberBoxBlock.vue'
import ShareButtonsBlock from '../../../shared/blocks/ShareButtonsBlock.vue'
import SidebarBlock from '../../../shared/blocks/SidebarBlock.vue'
import MenuBlock from '../../../shared/blocks/MenuBlock.vue'

import NumberCounterBlock from '../../../shared/blocks/NumberCounterBlock.vue'
import GroupBlock from '../../../shared/blocks/GroupBlock.vue'
import GroupCarouselBlock from '../../../shared/blocks/GroupCarouselBlock.vue'

// Interactive Blocks
import TabsBlock from '../../../shared/blocks/TabsBlock.vue'
// import TabItemBlock from '../blocks/TabItemBlock.vue' // REMOVED
import AccordionBlock from '../../../shared/blocks/AccordionBlock.vue'
// import AccordionItemBlock from '../blocks/AccordionItemBlock.vue' // REMOVED
import ToggleBlock from '../../../shared/blocks/ToggleBlock.vue'
import SliderBlock from '../../../shared/blocks/SliderBlock.vue'
// import SlideItemBlock from '../blocks/SlideItemBlock.vue' // REMOVED

// Forms Blocks
import SearchBlock from '../../../shared/blocks/SearchBlock.vue'
import LoginBlock from '../../../shared/blocks/LoginBlock.vue'
import ContactFormBlock from '../../../shared/blocks/ContactFormBlock.vue'
// import ContactFieldBlock from '../blocks/ContactFieldBlock.vue' // REMOVED
import SignupBlock from '../../../shared/blocks/SignupBlock.vue'
import NewsletterBlock from '../../../shared/blocks/NewsletterBlock.vue'

// Dynamic Blocks
import BlogBlock from '../../../shared/blocks/BlogBlock.vue'
import PortfolioBlock from '../../../shared/blocks/PortfolioBlock.vue'
import PostSliderBlock from '../../../shared/blocks/PostSliderBlock.vue'
import CommentsBlock from '../../../shared/blocks/CommentsBlock.vue'
import PostNavigationBlock from '../../../shared/blocks/PostNavigationBlock.vue'
import RelatedPostsBlock from '../../../shared/blocks/RelatedPostsBlock.vue'
import PostTitleBlock from '../../../shared/blocks/PostTitleBlock.vue'
import PostMetaBlock from '../../../shared/blocks/PostMetaBlock.vue'
import FeaturedImageBlock from '../../../shared/blocks/FeaturedImageBlock.vue'
import PostContentBlock from '../../../shared/blocks/PostContentBlock.vue'
import FilterablePortfolioBlock from '../../../shared/blocks/FilterablePortfolioBlock.vue'
import TabbedPostsBlock from '../../../shared/blocks/TabbedPostsBlock.vue'

// Fullwidth Blocks
import FullwidthHeaderBlock from '../../../shared/blocks/FullwidthHeaderBlock.vue'
import FullwidthSliderBlock from '../../../shared/blocks/FullwidthSliderBlock.vue'
import FullwidthPortfolioBlock from '../../../shared/blocks/FullwidthPortfolioBlock.vue'
import FullwidthCodeBlock from '../../../shared/blocks/FullwidthCodeBlock.vue'
import FullwidthImageBlock from '../../../shared/blocks/FullwidthImageBlock.vue'
import FullwidthMapBlock from '../../../shared/blocks/FullwidthMapBlock.vue'
import FullwidthMenuBlock from '../../../shared/blocks/FullwidthMenuBlock.vue'
import FullwidthPostContentBlock from '../../../shared/blocks/FullwidthPostContentBlock.vue'
import FullwidthPostSliderBlock from '../../../shared/blocks/FullwidthPostSliderBlock.vue'
import FullwidthPostTitleBlock from '../../../shared/blocks/FullwidthPostTitleBlock.vue'
import FullwidthSlideItemBlock from '../../../shared/blocks/FullwidthSlideItemBlock.vue'
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
    ModuleRegistry.registerComponent('richtext', RichTextBlock)
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
    ModuleRegistry.registerComponent('hero', HeroBlock)

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
    ModuleRegistry.registerComponent('tabbedposts', TabbedPostsBlock)

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
