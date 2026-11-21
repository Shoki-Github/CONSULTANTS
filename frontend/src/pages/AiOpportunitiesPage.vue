<script setup>
import { onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import PageBanner from '../components/PageBanner.vue'
import { aiOpportunities } from '../data/aiOpportunities'

const route = useRoute()

onMounted(() => {
  if (route.hash === '#evidence-to-action-pilot') {
    document.getElementById('evidence-to-action-pilot')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
})
</script>

<template>
  <div>
    <PageBanner title="AI &amp; MEL Solutions" breadcrumb="AI &amp; MEL" large />

    <section class="bg-white">
      <div class="mx-auto max-w-7xl px-5 py-14 md:py-16 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-ink md:text-4xl">AI expertise that moves evidence into action.</h2>
        <p class="mt-3 max-w-3xl leading-7 text-muted">
          CLM Consultants Limited combines artificial intelligence, monitoring, evaluation and learning to help organisations use their data, improve performance and make informed decisions.
        </p>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <article v-for="opportunity in aiOpportunities" :key="opportunity.title" :id="opportunity.id" class="content-card scroll-mt-24 p-5 md:p-6">
            <h3 class="text-lg font-bold leading-snug text-ink">{{ opportunity.title }}</h3>
            <p class="mt-2 text-sm leading-6 text-muted">{{ opportunity.description }}</p>
          </article>
        </div>

        <p class="mt-8 leading-7 text-muted">
          <strong class="text-ink">Ready to put your evidence to work?</strong>
          <RouterLink class="font-semibold text-brand underline underline-offset-2 hover:text-brand-dark" to="/contact">Contact CLM Consultants Limited</RouterLink>
          to discuss the AI and MEL support your organisation needs.
        </p>
      </div>
    </section>
  </div>
</template>
