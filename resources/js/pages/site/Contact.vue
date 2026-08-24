<script setup lang="ts">
import SectionHeading from '@/components/site/SectionHeading.vue';
import WhatsAppButton from '@/components/site/WhatsAppButton.vue';
import { useCompany } from '@/composables/useCompany';
import { useT } from '@/composables/useT';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { store } from '@/routes/contact';
import { Form } from '@inertiajs/vue3';

const { t } = useT();
const { company } = useCompany();

const inputClasses =
    'w-full rounded-site border border-site-outline-variant bg-site-surface-container-lowest px-4 py-2.5 text-sm text-site-on-surface placeholder:text-site-on-surface-variant focus:border-site-primary focus:ring-1 focus:ring-site-primary focus:outline-none';
</script>

<template>
    <SiteLayout
        :title="t('app.site.meta.contact_title')"
        :description="t('app.site.contact.subtitle')"
    >
        <div class="mx-auto max-w-6xl px-4 py-14 md:px-6 md:py-20">
            <SectionHeading
                :title="t('app.site.contact.title')"
                :subtitle="t('app.site.contact.subtitle')"
            />

            <div class="grid gap-10 lg:grid-cols-[2fr_1fr]">
                <!-- Formulário -->
                <Form
                    v-bind="store.form()"
                    :reset-on-success="[
                        'name',
                        'email',
                        'phone',
                        'subject',
                        'message',
                    ]"
                    v-slot="{ errors, processing }"
                    class="grid gap-5"
                >
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label
                                for="name"
                                class="mb-1.5 block text-sm font-medium text-site-on-surface"
                            >
                                {{ t('app.site.contact.name') }}
                            </label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                required
                                :class="inputClasses"
                            />
                            <p
                                v-if="errors.name"
                                class="mt-1 text-sm text-site-error"
                            >
                                {{ errors.name }}
                            </p>
                        </div>
                        <div>
                            <label
                                for="email"
                                class="mb-1.5 block text-sm font-medium text-site-on-surface"
                            >
                                {{ t('app.site.contact.email') }}
                            </label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                required
                                :class="inputClasses"
                            />
                            <p
                                v-if="errors.email"
                                class="mt-1 text-sm text-site-error"
                            >
                                {{ errors.email }}
                            </p>
                        </div>
                        <div>
                            <label
                                for="phone"
                                class="mb-1.5 block text-sm font-medium text-site-on-surface"
                            >
                                {{ t('app.site.contact.phone') }}
                            </label>
                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                :class="inputClasses"
                            />
                            <p
                                v-if="errors.phone"
                                class="mt-1 text-sm text-site-error"
                            >
                                {{ errors.phone }}
                            </p>
                        </div>
                        <div>
                            <label
                                for="subject"
                                class="mb-1.5 block text-sm font-medium text-site-on-surface"
                            >
                                {{ t('app.site.contact.subject') }}
                            </label>
                            <input
                                id="subject"
                                name="subject"
                                type="text"
                                :class="inputClasses"
                            />
                            <p
                                v-if="errors.subject"
                                class="mt-1 text-sm text-site-error"
                            >
                                {{ errors.subject }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label
                            for="message"
                            class="mb-1.5 block text-sm font-medium text-site-on-surface"
                        >
                            {{ t('app.site.contact.message') }}
                        </label>
                        <textarea
                            id="message"
                            name="message"
                            rows="6"
                            required
                            :class="inputClasses"
                        />
                        <p
                            v-if="errors.message"
                            class="mt-1 text-sm text-site-error"
                        >
                            {{ errors.message }}
                        </p>
                    </div>

                    <!-- Honeypot -->
                    <input
                        type="text"
                        name="website"
                        tabindex="-1"
                        autocomplete="off"
                        aria-hidden="true"
                        class="hidden"
                    />

                    <div>
                        <button
                            type="submit"
                            :disabled="processing"
                            class="rounded-site bg-site-primary px-8 py-3 text-sm font-medium text-site-on-primary shadow-md transition-opacity disabled:opacity-60"
                        >
                            {{
                                processing
                                    ? t('app.site.contact.sending')
                                    : t('app.site.contact.submit')
                            }}
                        </button>
                    </div>
                </Form>

                <!-- Dados da empresa -->
                <aside class="space-y-6">
                    <div
                        class="rounded-site-card bg-site-surface-container-low p-6"
                    >
                        <h2 class="font-display text-xl text-site-on-surface">
                            {{ t('app.site.contact.info_title') }}
                        </h2>
                        <ul
                            class="mt-4 space-y-3 text-sm text-site-on-surface-variant"
                        >
                            <li v-if="company.phone">
                                <a
                                    :href="`tel:${company.phone.replace(/\D/g, '')}`"
                                    class="hover:text-site-primary"
                                    >{{ company.phone }}</a
                                >
                            </li>
                            <li v-if="company.email">
                                <a
                                    :href="`mailto:${company.email}`"
                                    class="hover:text-site-primary"
                                    >{{ company.email }}</a
                                >
                            </li>
                        </ul>
                        <WhatsAppButton
                            :label="t('app.site.contact.whatsapp_button')"
                            class="mt-5"
                        />
                    </div>

                    <div
                        v-if="company.address"
                        class="rounded-site-card bg-site-surface-container-low p-6"
                    >
                        <h2 class="font-display text-xl text-site-on-surface">
                            {{ t('app.site.contact.address_title') }}
                        </h2>
                        <p
                            class="mt-3 text-sm leading-relaxed text-site-on-surface-variant"
                        >
                            {{ company.address }}
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    </SiteLayout>
</template>
