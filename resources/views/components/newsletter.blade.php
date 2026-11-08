@props([
    'content' => [],
])

@php
    $brand = $content['BRAND'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? 'https://wa.me/919876543210';
@endphp

<section id="contact" class="py-16 sm:py-24 bg-[#FCF8FA] border-b border-[#23121F]/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <x-reveal delay="100">
            <div class="rounded-3xl bg-white border border-[#23121F]/10 p-6 sm:p-10 lg:p-14 shadow-sm">
                
                <!-- Section Header -->
                <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-14">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#fde4f2] text-[#e68bbe] border border-[#f4b8da]/60 text-xs font-bold uppercase tracking-wider mb-3.5">
                        <span>★</span>
                        <span>ATELIER CONCIERGE & STYLING</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold font-serif text-[#22121E] tracking-tight leading-tight">
                        Make Your Home More Beautiful
                    </h2>
                    <p class="text-xs sm:text-sm sm:text-base text-[#6E5666] mt-3 leading-relaxed">
                        Have a question about bespoke drapery measurements, fabric samples, or custom bedding sets? Share your requirements and our interior styling concierge will guide you.
                    </p>
                </div>

                <!-- 2-Column Grid: Left Contact Form | Right Direct Assistance Cards -->
                <div
                    x-data="{
                        submitted: false,
                        name: '',
                        email: '',
                        phone: '',
                        inquiryType: 'Curtains & Drapes',
                        message: '',
                        submitForm() {
                            this.submitted = true;
                        }
                    }"
                    class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start"
                >
                    
                    <!-- Left: Elegant Form -->
                    <div class="lg:col-span-7 bg-[#FCF8FA] p-6 sm:p-8 rounded-2xl border border-[#23121F]/10">
                        <!-- Success Banner after Submission -->
                        <div x-show="submitted" x-cloak class="p-6 rounded-xl bg-[#F8EAF3] border border-[#e68bbe]/40 text-center">
                            <div class="w-12 h-12 rounded-full bg-[#e68bbe] text-white flex items-center justify-center mx-auto mb-3 shadow-sm text-xl font-bold">
                                ✓
                            </div>
                            <h3 class="text-lg font-bold font-serif text-[#22121E]">Inquiry Received with Thanks!</h3>
                            <p class="text-xs sm:text-sm text-[#6E5666] mt-1.5 leading-relaxed">
                                Dear <strong x-text="name || 'Valued Guest'"></strong>, our design master has received your note regarding <span x-text="inquiryType" class="text-[#22121E] font-semibold"></span> and will get in touch shortly via WhatsApp or Email.
                            </p>
                            <a
                                :href="'https://wa.me/919876543210?text=Hello%20Menahem!%20My%20name%20is%20' + encodeURIComponent(name) + '.%20I%20just%20submitted%20an%20inquiry%20regarding%20' + encodeURIComponent(inquiryType) + ':%20' + encodeURIComponent(message)"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#25d366] text-white text-xs font-bold hover:bg-[#20ba5a] transition-all shadow-xs"
                            >
                                <span>Continue on WhatsApp Immediately</span>
                                <span>→</span>
                            </a>
                        </div>

                        <!-- Contact Form -->
                        <form x-show="!submitted" @submit.prevent="submitForm()" class="space-y-4 sm:space-y-5">
                            <!-- Row 1: Name & Phone -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#22121E] uppercase tracking-wider mb-1.5">
                                        Your Full Name <span class="text-[#e68bbe]">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        x-model="name"
                                        required
                                        placeholder="e.g. Radhika Sharma"
                                        class="w-full px-4 py-3 rounded-xl bg-white border border-[#23121F]/15 text-xs sm:text-sm text-[#22121E] placeholder-[#6E5666]/50 focus:outline-hidden focus:border-[#e68bbe] focus:ring-1 focus:ring-[#e68bbe] transition-colors"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#22121E] uppercase tracking-wider mb-1.5">
                                        Phone / WhatsApp <span class="text-[#e68bbe]">*</span>
                                    </label>
                                    <input
                                        type="tel"
                                        x-model="phone"
                                        required
                                        placeholder="+91 98765 43210"
                                        class="w-full px-4 py-3 rounded-xl bg-white border border-[#23121F]/15 text-xs sm:text-sm text-[#22121E] placeholder-[#6E5666]/50 focus:outline-hidden focus:border-[#e68bbe] focus:ring-1 focus:ring-[#e68bbe] transition-colors"
                                    />
                                </div>
                            </div>

                            <!-- Row 2: Email & Category Selection -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#22121E] uppercase tracking-wider mb-1.5">
                                        Email Address <span class="text-[#e68bbe]">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        x-model="email"
                                        required
                                        placeholder="radhika@example.com"
                                        class="w-full px-4 py-3 rounded-xl bg-white border border-[#23121F]/15 text-xs sm:text-sm text-[#22121E] placeholder-[#6E5666]/50 focus:outline-hidden focus:border-[#e68bbe] focus:ring-1 focus:ring-[#e68bbe] transition-colors"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#22121E] uppercase tracking-wider mb-1.5">
                                        Interest / Requirement
                                    </label>
                                    <select
                                        x-model="inquiryType"
                                        class="w-full px-4 py-3 rounded-xl bg-white border border-[#23121F]/15 text-xs sm:text-sm text-[#22121E] focus:outline-hidden focus:border-[#e68bbe] focus:ring-1 focus:ring-[#e68bbe] transition-colors cursor-pointer"
                                    >
                                        <option value="Curtains & Drapes">Curtains & Custom Drapes</option>
                                        <option value="Bedsheets & Dohars">Bedsheets & Mulmul Dohars</option>
                                        <option value="Bath Towels & Linens">Bath Towels & Table Linens</option>
                                        <option value="Fabric Swatch Kit">Request Fabric Swatch Kit</option>
                                        <option value="Hospitality Bulk Order">Bulk / Hotel Inquiry</option>
                                        <option value="General Styling Advice">General Styling Advice</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 3: Message / Notes -->
                            <div>
                                <label class="block text-xs font-bold text-[#22121E] uppercase tracking-wider mb-1.5">
                                    Message or Specific Dimensions <span class="text-[#e68bbe]">*</span>
                                </label>
                                <textarea
                                    x-model="message"
                                    required
                                    rows="4"
                                    placeholder="Tell us about your room, window heights, fabric preferences, or questions..."
                                    class="w-full px-4 py-3 rounded-xl bg-white border border-[#23121F]/15 text-xs sm:text-sm text-[#22121E] placeholder-[#6E5666]/50 focus:outline-hidden focus:border-[#e68bbe] focus:ring-1 focus:ring-[#e68bbe] transition-colors resize-none"
                                ></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3">
                                <button
                                    type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl bg-[#23121F] hover:bg-[#341B2E] text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-sm hover:shadow-md cursor-pointer"
                                >
                                    <span>Send Consultation Request</span>
                                    <span>↗</span>
                                </button>
                                <span class="text-[11px] text-[#6E5666]">
                                    Protected by privacy guarantee • No spam
                                </span>
                            </div>
                        </form>
                    </div>

                    <!-- Right: Direct Styling Concierge Cards -->
                    <div class="lg:col-span-5 space-y-4">
                        
                        <!-- WhatsApp Direct Concierge Card -->
                        <div class="p-6 rounded-2xl bg-[#F8EAF3] border border-[#e68bbe]/30 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-[#e68bbe]">Fastest Response</span>
                                <h3 class="text-base sm:text-lg font-bold font-serif text-[#22121E] mt-1">
                                    Need Real-Time Guidance?
                                </h3>
                                <p class="text-xs text-[#6E5666] mt-1.5 leading-relaxed">
                                    Chat directly with our senior fabric stylist on WhatsApp. Share room photos for instant drapery sizing recommendations.
                                </p>
                            </div>
                            <div class="mt-4 pt-4 border-t border-[#23121F]/10">
                                <a
                                    href="{{ $whatsappUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="w-full flex items-center justify-center gap-2 py-3 px-5 rounded-xl bg-[#25d366] hover:bg-[#20ba5a] text-white text-xs font-bold tracking-wide transition-all shadow-sm"
                                >
                                    <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                    </svg>
                                    <span>Chat with Stylist on WhatsApp</span>
                                    <span>→</span>
                                </a>
                            </div>
                        </div>

                        <!-- Contact Details List -->
                        <div class="p-6 rounded-2xl bg-[#FCF8FA] border border-[#23121F]/10 space-y-3.5 text-xs">
                            <div class="flex items-start gap-3">
                                <span class="text-[#e68bbe] text-sm mt-0.5">📞</span>
                                <div>
                                    <strong class="block text-[#22121E]">Styling Helpline</strong>
                                    <span class="text-[#6E5666]">+91 98765 43210 (Mon – Sat, 9:30 AM – 8:00 PM IST)</span>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="text-[#e68bbe] text-sm mt-0.5">✉️</span>
                                <div>
                                    <strong class="block text-[#22121E]">Email Concierge</strong>
                                    <span class="text-[#6E5666]">care@menahemhome.in</span>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="text-[#e68bbe] text-sm mt-0.5">✂️</span>
                                <div>
                                    <strong class="block text-[#22121E]">Bespoke Tailoring Studio</strong>
                                    <span class="text-[#6E5666]">Pan-India door-to-door delivery & custom stitches</span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </x-reveal>

    </div>
</section>
