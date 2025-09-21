<script setup lang="ts">
import Navigation from '@/components/FrontEnd/Navigation/default.vue'
import Footer from '@/components/FrontEnd/Footer/default.vue'
import { onMounted, watch } from 'vue'
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
	host?: string
	title?: string
	slug?: string
	name?: string
	theme?: {
		primary: string
		secondary: string
		accent: string
	}
}>()

function applyTheme(theme?: Record<string, string>) {
	if (!theme) return
	for (const [key, value] of Object.entries(theme)) {
		document.documentElement.style.setProperty(`--${key}`, value)
	}
}

onMounted(() => {
  	if (props.theme) applyTheme(props.theme)
})
watch(() => props.theme, (newTheme) => {
  	if (newTheme) applyTheme(newTheme)
}, { deep: true })

// Eager import = no async = no flicker
const headerModules = import.meta.glob('/resources/js/components/Logo/*.vue', { eager: true })
const footerModules = import.meta.glob('/resources/js/components/LogoFull/*.vue', { eager: true })

function makeResolver(glob: Record<string, any>) {
	const map: Record<string, any> = {}
	for (const [key, mod] of Object.entries(glob)) {
		const file = key.split('/').pop()!
		const name = file.replace(/\.vue$/, '')
		map[name] = mod?.default ?? mod
	}
	return (slug: string) => map[slug] ?? map['default'] ?? Object.values(map)[0]
}

const resolveHeaderLogo = makeResolver(headerModules)
const resolveFooterLogo = makeResolver(footerModules)

const Logo = resolveHeaderLogo(props.slug)
const LogoFull = resolveFooterLogo(props.slug)

const address = {
	'text': 'Jalan Raya Sidemen No.88, Banjar Kebon, Kecamatan Sidemen, Karangasem, Bali 80864, Indonesia',
	'url': 'https://maps.app.goo.gl/iEBcrMRzupH8hx4Z8',
}

const footerDescription = "Rimba Luna is a secret hideaway inspired by Bali's natural beauty and deep character, where moonlit peacefulness blends with forest whispers."

const phone = {
	'text': '+62 361 4566 789',
	'url': 'tel:+623614566789',
}

const email = {
	'text': 'info@rimbaluna.com',
	'url': 'mailto:info@rimbaluna.com',
}

const whatsapp = {
	'text': '+62 881 123 456 789',
	'url': 'https://wa.me/62881123456789',
}

const footerText = "Rimba Luna, Karangasem, Bali, Indonesia"

const socials = [
	{
		label: 'Facebook',
		url: 'https://facebook.com'
	},
	{
		label: 'Twitter',
		url: 'https://twitter.com'
	},
	{
		label: 'Instagram',
		url: 'https://instagram.com'
	},
]

const primaryLinks = [
	{
		label: 'Deluxe Garden Cottage',
		url: '/accommodations/deluxe-garden-cottage',
	},
	{
		label: 'Premium Horizon Suite',
		url: '/accommodations/premium-horizon-suite',
	},
	{
		label: 'Family Pool Villa',
		url: '/accommodations/family-pool-villa',
	},
]

const discoverLinks = [
	{
		label: 'Accommodations',
		url: '/accommodations'
	},
	{
		label: 'Nearby Attractions',
		url: '/nearby-attractions'
	},
]

const companyLinks = [
	{
		label: 'About Us',
		url: '/about'
	},
	{
		label: 'Contact',
		url: '/contact'
	},
	{
		label: 'Privacy Policy',
		url: '/privacy-policy'
	},
	{
		label: 'Cancellation Policy',
		url: '/cancellation-policy'
	},
]

const navDiscoverLinks = [
	{
		label: 'Accommodations',
		url: '/accommodations'
	},
	{
		label: 'Nearby Attractions',
		url: '/nearby-attractions'
	},
]

const navCompanyLinks = [
	{
		label: 'About Us',
		url: '/about'
	},
	{
		label: 'Contact',
		url: '/contact'
	},
]
</script>

<template>
	<Head :title="`${title} - ${name}`" />
	
	<Navigation
		:host="host"
		:address="address"
		:whatsapp="whatsapp"
		:primaryLinks="primaryLinks"
		:navDiscoverLinks="navDiscoverLinks"
		:navCompanyLinks="navCompanyLinks"
	>
		<template #logo>
			<component :is="Logo" class="block h-full" />
		</template>
		<template #logoFull>
			<component :is="LogoFull" class="block h-16 md:h-20" />
		</template>
	</Navigation>

	<slot />

	<Footer
		:address="address"
		:footerDescription="footerDescription"
		:phone="phone"
		:email="email"
		:whatsapp="whatsapp"
		:footerText="footerText"
		:socials="socials"
		:discoverLinks="discoverLinks"
		:companyLinks="companyLinks"
	>
		<component :is="LogoFull" class="lg:h-[75px]" />
	</Footer>
</template>
