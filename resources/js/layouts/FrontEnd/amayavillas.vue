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
	'text': 'Jl. Raya Babakan Canggu, Canggu, Kec. Kuta Utara, Kabupaten Badung, Bali 80351',
	'url': 'https://maps.app.goo.gl/pyqEAbEL4G6uVNwA8',
}

const footerDescription = "Amaya Villas offers a private retreat in Bali. Thoughtfully designed villas provide an ideal place to rest, recharge, and experience the island at your own pace."

const phone = {
	'text': '+62 361 4566 789',
	'url': 'tel:+623614566789',
}

const email = {
	'text': 'info@amayavillas.com',
	'url': 'mailto:info@amayavillas.com',
}

const cta = {
	'enabled': true,
	'text': 'Reserve now',
	'url': '/villas',
}

const whatsapp = {
	'enabled': true,
	'text': '+62 881 123 456 789',
	'url': 'https://wa.me/62881123456789',
}

const footerText = "Villas Amaya, Bali, Indonesia"

const socials = [
	{
		label: 'Facebook',
		url: 'https://facebook.com'
	},
	{
		label: 'Instagram',
		url: 'https://instagram.com'
	},
]

const primaryLinks = [
	{
		label: 'Villa Imbuh',
		url: 'https://www.airbnb.com/rooms/1388037869552093989',
	},
	{
		label: 'Villa Marie',
		url: 'https://www.airbnb.com/rooms/1388033269552093966',
	},
	{
		label: 'Villa Léa',
		url: 'https://www.airbnb.com/rooms/1122009130202952180',
	},
]

const discoverLinks = [
	{
		label: 'Villas',
		url: '/villas'
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
		label: 'Villas',
		url: '/villas'
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
		:cta="cta"
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
