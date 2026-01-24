/**
 * Module Definitions - Complete Registry
 * Total: 82 modules
 */

import ModuleRegistry from '../core/ModuleRegistry'

// Structure (3)
import Section from './structure/Section'
import Row from './structure/Row'
import Column from './structure/Column'

// Basic (8)
import Heading from './basic/Heading'
import Text from './basic/Text'
import Button from './basic/Button'
import Image from './basic/Image'
import Icon from './basic/Icon'
import Divider from './basic/Divider'
import Spacer from './basic/Spacer'
import Logo from './basic/Logo'

// Media (9)
import Video from './media/Video'
import Gallery from './media/Gallery'
import Map from './media/Map'
import Audio from './media/Audio'
import BeforeAfter from './media/BeforeAfter'
import VideoPopup from './media/VideoPopup'
import Embed from './media/Embed'
import Lottie from './media/Lottie'
import VideoSlider from './media/VideoSlider'

// Content (31)
import Blurb from './content/Blurb'
import CTA from './content/CTA'
import Counter from './content/Counter'
import Testimonial from './content/Testimonial'
import SocialLinks from './content/SocialLinks'
import Countdown from './content/Countdown'
import ProgressBar from './content/ProgressBar'
import PricingTables from './content/PricingTables'
import Alert from './content/Alert'
import Code from './content/Code'
import TeamMember from './content/TeamMember'
import CircleCounter from './content/CircleCounter'
import IconList from './content/IconList'
import Breadcrumbs from './content/Breadcrumbs'
import Author from './content/Author'
import StarRating from './content/StarRating'
import TableOfContents from './content/TableOfContents'
import Quote from './content/Quote'
import LogoGrid from './content/LogoGrid'
import FAQ from './content/FAQ'
import Feature from './content/Feature'
import NumberBox from './content/NumberBox'
import ShareButtons from './content/ShareButtons'
import Sidebar from './content/Sidebar'
import Menu from './content/Menu'

import NumberCounter from './content/NumberCounter'
import Group from './content/Group'
import GroupCarousel from './content/GroupCarousel'
// Interactive (4)
import Tabs from './interactive/Tabs'
import Accordion from './interactive/Accordion'
import Toggle from './interactive/Toggle'
import Slider from './interactive/Slider'
// import TabItem from './interactive/TabItem' // REMOVED
// import AccordionItem from './interactive/AccordionItem' // REMOVED
// import SlideItem from './interactive/SlideItem' // REMOVED
// import VideoSlideItem from './media/VideoSlideItem' // REMOVED
// import GalleryItem from './media/GalleryItem' // REMOVED
// import FAQItem from './content/FAQItem' // REMOVED
// import LogoGridItem from './content/LogoGridItem' // REMOVED

// Forms
import Search from './forms/Search'
import Login from './forms/Login'
import ContactForm from './forms/ContactForm'
import Signup from './forms/Signup'
import Newsletter from './forms/Newsletter'

// Dynamic (11)
import Blog from './dynamic/Blog'
import Portfolio from './dynamic/Portfolio'
import PostSlider from './dynamic/PostSlider'
import Comments from './dynamic/Comments'
import PostNavigation from './dynamic/PostNavigation'
import RelatedPosts from './dynamic/RelatedPosts'
import PostTitle from './dynamic/PostTitle'
import PostMeta from './dynamic/PostMeta'
import FeaturedImage from './dynamic/FeaturedImage'
import PostContent from './dynamic/PostContent'
import FilterablePortfolio from './dynamic/FilterablePortfolio'

// Fullwidth (10)
import FullwidthHeader from './fullwidth/FullwidthHeader'
import FullwidthSlider from './fullwidth/FullwidthSlider'
import FullwidthPortfolio from './fullwidth/FullwidthPortfolio'
import FullwidthCode from './fullwidth/FullwidthCode'
import FullwidthImage from './fullwidth/FullwidthImage'
import FullwidthMap from './fullwidth/FullwidthMap'
import FullwidthMenu from './fullwidth/FullwidthMenu'
import FullwidthPostContent from './fullwidth/FullwidthPostContent'
import FullwidthPostSlider from './fullwidth/FullwidthPostSlider'
import FullwidthPostTitle from './fullwidth/FullwidthPostTitle'
import FullwidthMapPin from './fullwidth/FullwidthMapPin'
import FullwidthSlideItem from './fullwidth/FullwidthSlideItem'
import Hero from './content/Hero'
// import ContactField from './forms/ContactField' // REMOVED
import PricingFeature from './content/PricingFeature'
import ShareNetwork from './content/ShareNetwork'
// import SidebarWidget from './content/SidebarWidget' // REMOVED
import MapPin from './media/MapPin'

// Register all modules
const modules = [
    // Structure
    Section, Row, Column,
    // Basic
    Heading, Text, Button, Image, Icon, Divider, Spacer, Logo,
    // Media
    Video, Gallery, Map, Audio, BeforeAfter, VideoPopup, Embed, Lottie, VideoSlider,
    // Content
    Blurb, CTA, Counter, Testimonial, SocialLinks, Countdown, ProgressBar,
    PricingTables, Alert, Code, TeamMember, CircleCounter, IconList, Breadcrumbs,
    Author, StarRating, TableOfContents, Quote, LogoGrid, FAQ, Feature, NumberBox,
    ShareButtons, Sidebar, Menu, NumberCounter, Group, GroupCarousel,
    // Interactive
    Tabs, Accordion, Toggle, Slider,
    // Forms
    Search, Login, ContactForm, Signup, Newsletter,
    // Dynamic
    Blog, Portfolio, PostSlider, Comments, PostNavigation, RelatedPosts,
    PostTitle, PostMeta, FeaturedImage, PostContent, FilterablePortfolio,
    // Fullwidth
    FullwidthHeader, FullwidthSlider, FullwidthPortfolio, FullwidthCode, FullwidthImage,
    FullwidthMap, FullwidthMenu, FullwidthPostContent, FullwidthPostSlider, FullwidthPostTitle,
    FullwidthMapPin, FullwidthSlideItem, Hero, PricingFeature, ShareNetwork, MapPin
]

modules.forEach(m => ModuleRegistry.register(m))

export {
    // Structure
    Section, Row, Column,
    // Basic
    Heading, Text, Button, Image, Icon, Divider, Spacer, Logo,
    // Media
    Video, Gallery, Map, Audio, BeforeAfter, VideoPopup, Embed, Lottie, VideoSlider,
    // Content
    Blurb, CTA, Counter, Testimonial, SocialLinks, Countdown, ProgressBar,
    PricingTables, Alert, Code, TeamMember, CircleCounter, IconList, Breadcrumbs,
    Author, StarRating, TableOfContents, Quote, LogoGrid, FAQ, Feature, NumberBox,
    ShareButtons, Sidebar, Menu, NumberCounter, Group, GroupCarousel,
    // Interactive
    Tabs, Accordion, Toggle, Slider,
    // Forms
    Search, Login, ContactForm, Signup, Newsletter,
    // Dynamic
    Blog, Portfolio, PostSlider, Comments, PostNavigation, RelatedPosts,
    PostTitle, PostMeta, FeaturedImage, PostContent, FilterablePortfolio,
    // Fullwidth
    FullwidthHeader, FullwidthSlider, FullwidthPortfolio, FullwidthCode, FullwidthImage,
    FullwidthMap, FullwidthMenu, FullwidthPostContent, FullwidthPostSlider, FullwidthPostTitle,
    FullwidthMapPin, FullwidthSlideItem, Hero, PricingFeature, ShareNetwork, MapPin
}
