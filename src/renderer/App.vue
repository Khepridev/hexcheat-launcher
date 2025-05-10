<template>
  <!-- Loading Screen -->
  <div v-if="isLoading || !isPageReady || connectionError || (manifest?.maintenance?.enabled === 1)" 
       class="fixed inset-0 w-[840px] h-[520px] bg-black/90 flex items-center justify-center rounded-[6px]">
    <div class="flex flex-col items-center">
      <!-- Loading spinner -->
      <template v-if="isLoading && !connectionError">
        <div class="w-16 h-16 border-4 border-blue-500/40 border-t-transparent rounded-full animate-spin"></div>
        <span class="text-white/70 mt-4">Loading...</span>
      </template>
      
      <!-- Bakım modu -->
      <template v-else-if="manifest?.maintenance?.enabled === 1">
        <div class="absolute top-0 left-0 w-full h-8 bg-transparent z-40 draggable"></div>
        <div class="absolute top-0 right-0 flex items-center z-50">
          <button @click="minimizeWindow" class="w-12 h-8 flex items-center justify-center text-white/50 hover:bg-white/10 no-drag">
            <div class="w-4 h-[1px] bg-current"></div>
          </button>
          <button @click="closeWindow" 
                  class="w-12 h-8 flex items-center justify-center text-white/50 hover:bg-red-500/40 no-drag rounded-tr-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="text-yellow-500 text-6xl mb-4">
          <i class="fas fa-tools"></i>
        </div>
        <div class="text-white/70 text-center" v-html="manifest.maintenance.message[currentLang]"></div>
        <button @click="closeWindow" 
                class="mt-6 px-4 py-2 bg-blue-500/20 hover:bg-blue-500/30 text-white/90 rounded-lg transition-all">
          {{ t('close') }}
        </button>
      </template>
      
      <!-- Bağlantı hatası -->
      <template v-else-if="connectionError">
        <div class="absolute top-0 left-0 w-full h-8 bg-transparent z-40 draggable"></div>
        <div class="absolute top-0 right-0 flex items-center z-50">
          <button @click="minimizeWindow" class="w-12 h-8 flex items-center justify-center text-white/50 hover:bg-white/10 no-drag">
            <div class="w-4 h-[1px] bg-current"></div>
          </button>
          <button @click="closeWindow" 
                  class="w-12 h-8 flex items-center justify-center text-white/50 hover:bg-red-500/40 no-drag rounded-tr-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="text-red-500 text-6xl mb-4">
          <i class="fas fa-exclamation-circle"></i>
        </div>
        <span class="text-white/70 text-center">Connection failed.<br/>Please check your internet connection.</span>
        <button @click="closeWindow" 
                class="mt-6 px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-white/90 rounded-lg transition-all">
          {{ t('close') }}
        </button>
      </template>
    </div>
  </div>

  <!-- Main Content -->
  <div v-else-if="manifest" class="fixed inset-0 w-[840px] h-[520px] overflow-hidden rounded-lg shadow-2xl border border-white/10">
    <!-- Müzik çalar elementini sayfalardan bağımsız olarak her zaman göster -->
    <audio 
      ref="audioPlayer"
      :src="manifest?.mp3_player_control?.enabled === 1 ? manifest?.mp3_player_control?.player?.[0]?.url || '' : ''"
      @timeupdate="onTimeUpdate"
      @loadedmetadata="onLoadedMetadata"
      @canplaythrough="onCanPlayThrough"
      style="display: none;"
      key="audio-player"
      preload="auto"
    ></audio>
    
    <!-- Sürüklenebilir header -->
    <div class="absolute top-0 left-0 w-full h-8 bg-transparent z-40 draggable"></div>

    <!-- Arka plan -->
    <div class="absolute inset-0">
      <!-- Video Arka Plan -->
      <video
        v-if="background.mode === 2"
        class="absolute inset-0 w-full h-full object-cover"
        autoplay
        loop
        muted
        playsinline
        preload="auto"
        ref="videoPlayer"
      >
        <source :src="background.videoUrl" type="video/mp4">
      </video>

      <!-- Resim Arka Plan -->
      <div 
        v-if="background.mode === 1"
        class="absolute inset-0 bg-cover bg-center transition-all duration-700"
        :style="{ backgroundImage: `url(${background.imageUrl})` }"
      ></div>

      <!-- Karartma overlay -->
      <div class="absolute inset-0 bg-black/60"></div>
    </div>

    <!-- Pencere kontrolleri -->
    <div class="absolute top-0 right-0 flex items-center z-50">
      <button @click="minimizeWindow" class="w-12 h-8 flex items-center justify-center text-white/50 hover:bg-white/10 no-drag">
        <div class="w-4 h-[1px] bg-current"></div>
      </button>
      <button @click="closeWindow" 
              class="w-12 h-8 flex items-center justify-center text-white/50 hover:bg-red-500/40 no-drag rounded-tr-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <!-- Ana içerik -->
    <div class="relative h-full flex flex-col">
      <!-- Menu Items ve Dil Seçimi -->
      <div class="p-4 mt-4">
        <div class="flex gap-2 items-center">
          <!-- Logo ve Başlık Skeleton -->
          <div v-if="isButtonsLoading" class="flex items-center gap-2">
            <div class="w-7 h-7 bg-white/5 rounded animate-pulse"></div>
            <div class="h-5 w-24 bg-white/5 rounded animate-pulse"></div>
          </div>

          <!-- Gerçek Logo ve Başlık -->
          <div v-else class="flex items-center gap-2">
            <a v-if="manifest?.logo?.url"
              @click.prevent="handleMenuItemClick(manifest.logo.link)"
              href="#"
              class="flex items-center gap-2 select-none"
              @dragstart.prevent>
              <!-- Logo -->
              <img :src="manifest.logo.url"
                :style="{
                  width: `${manifest.logo.width}px`,
                  height: `${manifest.logo.height}px`
                }"
                alt="Logo"
                class="select-none pointer-events-none" />
              <!-- Logo Text -->
              <div v-if="manifest?.logo?.text?.enabled === 1"
                  class="logo-text select-none pointer-events-none"
                    v-html="manifest.logo.text.content">
              </div>
            </a>
          </div>

          <!-- Menu Items Skeleton -->
          <template v-if="isButtonsLoading">
            <div class="flex gap-2">
              <!-- Home Button Skeleton -->
              <div class="w-16 h-7 bg-white/5 rounded animate-pulse"></div>
              
              <!-- Menu Items Skeletons -->
              <div v-for="i in 3" :key="i" 
                   class="w-20 h-7 bg-white/5 rounded animate-pulse">
              </div>
              
            </div>
          </template>

          <!-- Gerçek Menu Items -->
          <template v-else-if="currentPage === 'home' || currentPage === 'apps'">
            <a @click="() => { currentPage = 'home'; restoreAudioState(); }"
               href="#"
               class="hover:text-white text-sm transition-colors hover:scale-85 transform duration-200 px-2 py-1 rounded-md hover:bg-black/50 select-none cursor-pointer"
               :class="{ 'bg-black/50 text-white': currentPage === 'home', 'text-white/70': currentPage !== 'home' }">
               {{ t('home') }}
            </a>
             <!-- Apps Button -->
             <a @click="() => { currentPage = 'apps'; restoreAudioState(); }"
               href="#"
               class="hover:text-white text-sm transition-colors hover:scale-85 transform duration-200 px-2 py-1 rounded-md hover:bg-black/50 select-none cursor-pointer"
               :class="{ 'bg-black/50 text-white': currentPage === 'apps', 'text-white/70': currentPage !== 'apps' }">
               {{ t('apps') }}
            </a>

            <a v-for="item in menuItems" 
               :key="item.id"
               @click.prevent="handleMenuItemClick(item.url)"
               href="#"
               class="hover:text-white text-sm transition-colors hover:scale-105 transform duration-200 px-2 py-1 rounded-md hover:bg-black/50 select-none cursor-pointer text-white/70">
              {{ item.title }}
            </a>

          </template>

          <!-- Settings Back Button Skeleton -->
          <template v-else-if="currentPage === 'settings' && isButtonsLoading">
            <div class="flex items-center gap-1">
              <div class="w-16 h-7 bg-white/5 rounded animate-pulse"></div>
              <div class="w-20 h-5 bg-white/5 rounded animate-pulse ml-2"></div>
            </div>
          </template>

          <!-- Gerçek Settings Back Button -->
          <template v-else-if="currentPage === 'settings'">
            <div class="flex items-center gap-1">
              <button 
                @click="() => { currentPage = 'home'; restoreAudioState(); }"
                class="text-white/70 hover:text-white text-sm transition-colors hover:scale-105 transform duration-200 px-2 h-6 rounded-md hover:bg-black/50 select-none flex items-center gap-2"
              >
                <i class="fa-light fa-angle-left"></i>
                {{ t('back') }}
              </button>
              <span class="text-white/90 text-sm font-medium select-none">
                {{ t('settings') }}
              </span>
            </div>
          </template>

          <!-- Dil Seçimi Skeleton -->
          <div v-if="isButtonsLoading" class="relative ml-auto">
            <div class="w-24 h-8 bg-white/5 rounded animate-pulse"></div>
          </div>

          <!-- Gerçek Dil Seçimi -->
          <div v-else class="relative ml-auto">
            <button 
              @click="showLangMenu = !showLangMenu"
              class="text-white/70 hover:text-white text-sm px-3 py-1.5 rounded-md hover:bg-white/10 
                     transition-all duration-200 flex items-center gap-2.5 select-none group"
            >
              <svg class="w-5 h-5 transition-transform duration-300 group-hover:rotate-12" viewBox="0 0 24 24" fill="none">
                <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" 
                      class="stroke-current" stroke-width="1.5"/>
                <path d="M7 12C7 14.7614 9.23858 17 12 17C14.7614 17 17 14.7614 17 12C17 9.23858 14.7614 7 12 7" 
                      class="stroke-current" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M12 2V22" class="stroke-current" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M2 12H22" class="stroke-current" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M4 7H11" class="stroke-current" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M13 17H20" class="stroke-current" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              <span class="relative top-px">{{ availableLanguages.find(l => l.code === currentLang)?.name }}</span>
              <svg class="w-3.5 h-3.5 transition-transform duration-200" 
                   :class="{ 'rotate-180': showLangMenu }"
                   viewBox="0 0 24 24" fill="none">
                <path d="M6 9L12 15L18 9" 
                      class="stroke-current" 
                      stroke-width="2" 
                      stroke-linecap="round" 
                      stroke-linejoin="round"/>
              </svg>
            </button>

            <!-- Dil Menüsü -->
            <div v-if="showLangMenu" 
                 class="absolute right-0 mt-1 bg-black/80 backdrop-blur-sm rounded-md py-1 min-w-[110px] 
                        z-50 border border-white/10 shadow-lg shadow-black/50 select-none">
              <button 
                v-for="lang in availableLanguages" 
                :key="lang.code"
                @click="changeLang(lang.code)"
                class="w-full text-left px-4 py-2 text-sm text-white/70 hover:text-white 
                       hover:bg-white/10 transition-colors select-none flex items-center gap-3"
                :class="{ 'bg-white/5': currentLang === lang.code }"
              >                
                {{ lang.name }}
                <span class="w-2 h-2 flex items-center justify-center">
                  <i v-if="currentLang === lang.code"  class="text-xs font-light fa-regular fa-arrow-up-right"></i>                  
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Sayfa İçerikleri -->
      <transition name="fade" mode="out-in">
        <!-- Ana Sayfa -->
        <div v-if="currentPage === 'home'" class="relative flex-1">

          <!-- Important Notice Skeleton -->
          <div v-if="isButtonsLoading && manifest?.importantNotice?.enabled === 1" 
               class="mx-4 p-3 rounded-md border border-white/10 bg-white/5 animate-pulse">
            <div class="flex flex-col gap-2">
              <div class="h-4 w-3/4 bg-white/10 rounded"></div>
              <div class="h-3 w-24 bg-white/10 rounded"></div>
            </div>
          </div>

          <!-- Gerçek Important Notice -->
          <div v-else-if="manifest?.importantNotice && manifest.importantNotice.enabled === 1" 
               :class="{
                 'bg-green-500/30 border-green-500/20': manifest.importantNotice.type === 'success',
                 'bg-red-500/30 border-red-500/20 shadow-md shadow-red-500/20': manifest.importantNotice.type === 'danger',
                 'bg-yellow-500/30 border-yellow-500/20 shadow-md shadow-yellow-500/20': manifest.importantNotice.type === 'warning',
                 'bg-blue-500/30 border-blue-500/20 shadow-md shadow-blue-500/20': manifest.importantNotice.type === 'info',
                 'bg-slate-200/10 border-slate-700/20 shadow-md shadow-slate-500/10': manifest.importantNotice.type === 'while',
                 'bg-black/50 border-slate-950/20 shadow-md shadow-black/20': manifest.importantNotice.type === 'black'
               }"
               class="mx-4 p-3 rounded-md border">
            <div class="flex flex-col">
              <div class="text-white/70 text-xs" v-html="formatMessage(manifest.importantNotice.message)"></div>
              <span class="text-white/50 text-[10px] mt-1 block">{{ formatDate(manifest.importantNotice.date) }}</span>
            </div>
          </div>

          <!-- Mp3 Player Skeleton -->
          <div v-if="isButtonsLoading && manifest?.mp3_player_control?.enabled === 1" 
               class="mx-4 mt-3 p-3 rounded-md border border-white/10 bg-black/40 backdrop-blur-sm">
            <div class="flex items-center gap-3">
              <!-- Görsel Skeleton -->
              <div class="w-12 h-12 bg-white/5 rounded-lg animate-pulse"></div>
              
              <div class="flex-1">
                <!-- Başlık Skeleton -->
                <div class="h-4 w-48 bg-white/5 rounded animate-pulse"></div>
                
                <!-- Progress Bar Skeleton -->
                <div class="mt-2">
                  <div class="h-1 bg-white/5 rounded-full"></div>
                  
                  <!-- Süre ve Kontroller Skeleton -->
                  <div class="flex items-center justify-between mt-1">
                    <div class="flex gap-2">
                      <div class="h-3 w-12 bg-white/5 rounded animate-pulse"></div>
                      <div class="h-3 w-12 bg-white/5 rounded animate-pulse"></div>
                    </div>
                    <div class="h-4 w-4 bg-white/5 rounded animate-pulse"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Gerçek Mp3 Player -->
          <div v-else-if="manifest?.mp3_player_control?.enabled === 1" 
               class="mx-4 mt-3 p-3 rounded-md border border-white/10 bg-black/40 backdrop-blur-sm">
            <div class="flex items-center gap-3">
              <!-- Görsel -->
              <img :src="manifest.mp3_player_control.player[0].image" 
                   class="w-12 h-12 rounded-lg object-cover"
                   :alt="manifest.mp3_player_control.player[0].title">
              
              <div class="flex-1">
                <!-- Başlık -->
                <div class="text-white/90 text-sm font-medium">
                  {{ manifest.mp3_player_control.player[0].title }}
                </div>
                
                <!-- Progress Bar ve Süre -->
                <div class="mt-1">
                  <div class="h-1 bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500/50 transition-all duration-300"
                         :style="{ width: `${audioProgress}%` }">
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-between mt-1">
                    <!-- Süre -->
                    <div class="flex gap-2 text-xs text-white/50">
                      <span>{{ formatTime(currentTime) }}</span>
                      <span>/</span>
                      <span>{{ formatTime(duration) }}</span>
                    </div>

                    <!-- Ses Kontrolü -->
                    <div class="relative">
                      <button @click.stop="toggleVolumeMenu"
                              class="text-white/50 hover:text-white/80 transition-colors">
                        <i class="fas" :class="{
                          'fa-volume-up': volume > 50,
                          'fa-volume-down': volume > 0 && volume <= 50,
                          'fa-volume-mute': volume <= 0
                        }"></i>
                      </button>

                      <!-- Ses Kontrol Menüsü -->
                      <div v-show="showVolumeControl" 
                           class="absolute bottom-6 right-0 p-2 w-32 bg-black/80 backdrop-blur-sm rounded-lg border border-white/10 z-50"
                           @click.stop>
                        <div class="flex flex-col gap-2">
                          <div class="text-xs text-white/50 text-center">{{ t('volume') }}</div>
                          
                          <input type="range" 
                                 v-model="volume" 
                                 min="0" 
                                 max="100"
                                 @input="updateVolume" 
                                 class="w-full h-1 bg-white/10 rounded-full appearance-none cursor-pointer">
                          
                          <div class="flex items-center justify-between text-xs text-white/50">
                            <span>{{ volume }}%</span>
                            <button @click="toggleMute" 
                                    class="hover:text-white/80 transition-colors">
                              {{ volume === 0 ? t('unmute') : t('mute') }}
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- News Section -->
          <div class="absolute bottom-16 left-4 right-4">
            
            <!-- News Header ve Sosyal Medya Skeleton -->
            <div v-if="isButtonsLoading" class="flex items-center justify-between mb-2">
              <!-- News Başlık Skeleton -->
              <div v-if="news && news.length > 0" class="w-16 h-5 bg-white/5 rounded animate-pulse"></div>
              
              <!-- Sosyal Medya Skeleton -->
              <div class="flex gap-3 items-center ml-auto">
                <div v-for="i in (manifest?.socialMedia?.length || 4)" :key="i" 
                     class="w-8 h-8 bg-white/5 rounded-full animate-pulse">
                </div>
              </div>
            </div>

            <!-- Gerçek News Header ve Sosyal Medya -->
            <div v-else class="flex items-center justify-between mb-2">
              <h2 v-if="news && news.length > 0" class="text-white/90 text-sm font-medium select-none">
                {{ t('news') }}
              </h2>
              
              <!-- Gerçek Sosyal Medya -->
              <div class="flex gap-3 items-center ml-auto">
                <div v-for="(social, index) in socialMediaItems" 
                     :key="social.id"
                     class="relative group">
                  <a @click.prevent="handleMenuItemClick(social.url)"
                     href="#"
                     :class="getSocialMediaColor(social.icon)"
                     class="text-white/50 transition-all duration-300 hover:scale-110">
                    <i :class="social.icon" class="text-lg"></i>
                  </a>
                  <!-- Tooltip -->
                  <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2.5 py-1.5 
                              bg-[#111111]/90 backdrop-blur-sm border border-white/10 
                              rounded-lg text-xs text-white/70 whitespace-nowrap
                              opacity-0 group-hover:opacity-100 transition-all duration-200 
                              pointer-events-none transform scale-95 group-hover:scale-100"
                              :class="{ '-translate-x-[80%]': index === socialMediaItems.length - 1 }">
                    {{ social.title }}
                    <!-- Tooltip Arrow -->
                    <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 
                                border-4 border-transparent border-t-[#111111]/90"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- News Cards Skeleton -->
            <div v-if="isButtonsLoading && news && news.length > 0" class="relative">
              <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-custom scroll-smooth">
                <div v-for="i in (news.length || 4)" :key="i" 
                     class="relative w-40 h-24 bg-white/5 rounded animate-pulse flex-shrink-0">
                  <!-- Gradient overlay skeleton -->
                  <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                  
                  <!-- Text content skeleton -->
                  <div class="absolute bottom-0 left-0 p-2">
                    <div class="w-32 h-3 bg-white/10 rounded mb-1"></div>
                    <div class="w-20 h-2 bg-white/10 rounded"></div>
                  </div>
                </div>
              </div>
            </div>          
            

            <!-- Gerçek News Cards -->
            <div v-else-if="news && news.length > 0" class="relative">
              <div ref="newsContainer" 
                   class="flex gap-2 overflow-x-auto pb-2 scrollbar-custom scroll-smooth select-none">

                <div v-for="item in news" 
                     :key="item.id" 
                     @click="openNewsLink(item.url)"
                     class="group relative w-40 h-24 rounded overflow-hidden cursor-pointer flex-shrink-0 select-none">
                  <img :src="item.image" 
                       :alt="item.title" 
                       draggable="false"
                       class="w-full h-full object-cover pointer-events-none transition-transform duration-300 group-hover:scale-110" />
                  <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent opacity-80 pointer-events-none"></div>
                  <div class="absolute bottom-0 left-0 p-2 pointer-events-none">
                    <h3 class="text-white text-xs font-medium leading-tight">{{ item.title }}</h3>
                    <p class="text-white/70 text-[10px] mt-0.5">{{ formatDate(item.date) }}</p>
                  </div>
                </div>
              </div>
              
              <!-- Sağa/Sola Git Butonları -->
              <div v-if="news.length > 4">
                <!-- Sol Buton -->
                <div v-show="canScrollLeft"
                     class="absolute left-0 top-0 h-24 w-[38px] bg-gradient-to-r from-black/60 to-transparent">
                  <button @click="scrollNews('left')"
                          @mousedown="startAutoScroll('left')"
                          @mouseup="stopAutoScroll"
                          @mouseleave="stopAutoScroll"
                          class="absolute inset-0 flex items-center justify-center 
                                 group transition-all duration-300 hover:bg-black/20">
                    <svg class="w-5 h-5 text-white/70 group-hover:text-white transform group-hover:scale-110 transition-all duration-300 rotate-180" 
                         fill="none" 
                         stroke="currentColor" 
                         viewBox="0 0 24 24">
                      <path stroke-linecap="round" 
                            stroke-linejoin="round" 
                            stroke-width="2" 
                            d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </div>

                <!-- Sağ Buton -->
                <div v-show="canScrollRight"
                     class="absolute right-0 top-0 h-24 w-[38px] bg-gradient-to-l from-black/60 to-transparent">
                  <button @click="scrollNews('right')"
                          @mousedown="startAutoScroll('right')"
                          @mouseup="stopAutoScroll"
                          @mouseleave="stopAutoScroll"
                          class="absolute inset-0 flex items-center justify-center 
                                 group transition-all duration-300 hover:bg-black/20">
                    <svg class="w-5 h-5 text-white/70 group-hover:text-white transform group-hover:scale-110 transition-all duration-300" 
                         fill="none" 
                         stroke="currentColor" 
                         viewBox="0 0 24 24">
                      <path stroke-linecap="round" 
                            stroke-linejoin="round" 
                            stroke-width="2" 
                            d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Ana Sayfa için Skeleton -->
        <div v-if="currentPage === 'home' && !news.length" class="relative flex-1 page-margin">
          <div class="glass-effect rounded-2xl mt-2">
            <!-- News Skeleton -->
            <div class="p-4">
              <div class="flex items-center justify-between mb-4">
                <div class="h-5 w-24 bg-gray-700/20 rounded animate-pulse"></div>
              </div>
              <div class="flex gap-3">
                <div v-for="i in 4" :key="i" 
                     class="w-[180px] h-[96px] bg-gray-700/20 rounded animate-pulse">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Apps Sayfası -->
        <div v-else-if="currentPage === 'apps'" class="relative flex-1 page-margin">
          <div class="glass-effect rounded-2xl mt-2">          
            <!-- Apps Header -->
            
            <!-- Apps List Container -->
            <div class="max-h-[380px] overflow-y-auto scrollbar-custom p-4">
              
              <div class="space-y-3">
                <div v-for="app in manifest?.apps" 
                     :key="app.id"
                     class="flex items-center gap-3 h-[52px] px-4 rounded-[6px] bg-black/60 backdrop-blur-sm border border-[#232323]/50 
                            transition-all duration-300 hover:bg-black/40">
                  <!-- App Icon -->
                  <img :src="app.icon" 
                       :alt="app.name"
                       class="w-7 h-7 rounded-[4px] object-cover" />
                  
                  <!-- App Info -->
                  <div class="flex-1 flex items-center justify-between">
                    <div class="flex flex-col">
                      <span class="text-white/90 text-sm">{{ app.name }}</span>
                      <span class="text-white/50 text-xs">{{ formatSize(calculateAppSize(app)) }}</span>
                    </div>

                    <!-- Version Badge -->
                    <span class="text-[10px] px-2 py-0.5 rounded"
                          :class="{
                            'bg-gradient-to-r from-[#ffffff05] to-[#666666] text-white': app.version.startsWith('1'),
                            'bg-gradient-to-r from-[#3498db05] to-[#2980b9] text-white': app.version.startsWith('2'),
                            'bg-gradient-to-r from-[#e74c3c05] to-[#c0392b] text-white': app.version.startsWith('3'),
                            'bg-gradient-to-r from-[#9b59b605] to-[#8e44ad] text-white': app.version.startsWith('4'),
                            'bg-gradient-to-r from-[#f1c40f05] to-[#f39c12] text-white': app.version.startsWith('5'),
                            'bg-gradient-to-r from-[#2ecc7105] to-[#27ae60] text-white': app.version.startsWith('6'),
                            'bg-gradient-to-r from-[#1abc9c05] to-[#16a085] text-white': app.version.startsWith('7')
                          }">
                      v{{ app.version }}
                    </span>

                    <!-- Download/Update/Start Button -->
                    <button 
                      @click="handleAppAction(app)"
                      :class="{
                        'px-2 py-1 rounded transition-all duration-300 text-xs font-medium': true,
                        'bg-green-500/30 hover:bg-green-500/40 text-white/90': !isDownloading && getButtonTextForApp(app) === t('download'),
                        'bg-yellow-500/30 hover:bg-yellow-500/40 text-white/90': !isDownloading && getButtonTextForApp(app) === t('update'),
                        'bg-blue-500/30 hover:bg-blue-500/40 text-white/90': !isDownloading && getButtonTextForApp(app) === t('start'),
                        'bg-black/40 text-white/70 cursor-wait': isDownloading
                      }"
                    >
                      <span v-if="isDownloading">{{ t('downloading') }}</span>
                      <span v-else>{{ getButtonTextForApp(app) }}</span>
                  </button>

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Settings Sayfası -->
        <div v-else-if="currentPage === 'settings'" class="relative flex-1 p-4 page-margin">
          <div class="bg-black/40  rounded-lg p-4 max-h-[380px] overflow-y-auto settings-scrollbar">
            
            <!-- Kurulum Yolu -->
            <div class="mb-4">
              <label class="block text-white mb-2 select-none flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                {{ t('installPath') }}
              </label>
              <div class="flex gap-2">
                <input type="text" 
                       v-model="installPath" 
                       class="flex-1 bg-black/30 border border-white/10 rounded-lg px-2 py-1 text-white" 
                       readonly />
                <button @click="selectInstallPath" 
                        class="px-4 py-2 rounded-lg bg-blue-500/20 hover:bg-blue-500/50 text-white transition-all select-none">
                  {{ t('select') }}
                </button>
              </div>
            </div>

            <!-- Bildirim Ayarları -->
            <div class="space-y-4">
              <!-- Genel Bildirimler -->
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-white font-medium mb-1 select-none flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    {{ t('notifications') }}
                  </h3>
                  <p class="text-white/50 text-sm select-none ml-7">
                    {{ notificationsEnabled ? t('notificationsEnabled') : t('notificationsDisabled') }}
                  </p>
                </div>
                <button @click="toggleNotifications" 
                        class="w-12 h-6 rounded-full relative transition-colors select-none"
                        :class="notificationsEnabled ? 'bg-blue-500/30' : 'bg-white/10'">
                  <div class="w-5 h-5 rounded-full bg-white absolute top-0.5 transition-transform"
                       :class="notificationsEnabled ? 'translate-x-6' : 'translate-x-0.5'">
                  </div>
                </button>
              </div>

              <!-- Haber Bildirimleri -->
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-white font-medium mb-1 select-none flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                    {{ t('newsNotifications') }}
                  </h3>
                  <p class="text-white/50 text-sm select-none ml-7">
                    {{ newsNotificationsEnabled ? t('newsNotificationsEnabled') : t('newsNotificationsDisabled') }}
                  </p>
                </div>
                <button @click="toggleNewsNotifications" 
                        class="w-12 h-6 rounded-full relative transition-colors select-none"
                        :class="newsNotificationsEnabled ? 'bg-blue-500/30' : 'bg-white/10'">
                  <div class="w-5 h-5 rounded-full bg-white absolute top-0.5 transition-transform"
                       :class="newsNotificationsEnabled ? 'translate-x-6' : 'translate-x-0.5'">
                  </div>
                </button>
              </div>

              <!-- Güncelleme Bildirimleri -->
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-white font-medium mb-1 select-none flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    {{ t('updateNotifications') }}
                  </h3>
                  <p class="text-white/50 text-sm select-none ml-7">
                    {{ updateNotificationsEnabled ? t('updateNotificationsEnabled') : t('updateNotificationsDisabled') }}
                  </p>
                </div>
                <button
                  @click="toggleUpdateNotifications"
                  class="w-12 h-6 rounded-full relative transition-colors duration-200"
                  :class="updateNotificationsEnabled ? 'bg-blue-500/30' : 'bg-white/10'"
                >
                  <div
                    class="w-5 h-5 rounded-full bg-white absolute top-0.5 transition-transform duration-200"
                    :class="updateNotificationsEnabled ? 'translate-x-6' : 'translate-x-0.5'"
                  ></div>
                </button>
              </div>

            </div>

            <!-- Contact Links -->
            <div class="mt-8 flex justify-end items-center gap-4">
              <!-- Website Link -->
              <a @click.prevent="handleMenuItemClick('https://khepridev.xyz/')" 
                 href="#" 
                 class="flex items-center gap-1.5 text-white/60 hover:text-white transition-colors">  
                <span class="text-xs">khepridev.xyz</span>
              </a>
              
              <!-- GitHub Link -->
              <a @click.prevent="handleMenuItemClick('https://github.com/Khepridev')" 
                 href="#" 
                 class="flex items-center gap-1.5 text-white/60 hover:text-white transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 2C6.475 2 2 6.475 2 12C2 16.425 4.8625 20.1625 8.8375 21.4875C9.3375 21.575 9.525 21.275 9.525 21.0125C9.525 20.775 9.5125 19.9875 9.5125 19.15C7 19.6125 6.35 18.5375 6.15 17.975C6.0375 17.6875 5.55 16.8 5.125 16.5625C4.775 16.375 4.275 15.9125 5.1125 15.9C5.9 15.8875 6.4625 16.625 6.65 16.925C7.55 18.4375 8.9875 18.0125 9.5625 17.75C9.65 17.1 9.9125 16.6625 10.2 16.4125C7.975 16.1625 5.65 15.3 5.65 11.475C5.65 10.3875 6.0375 9.4875 6.675 8.7875C6.575 8.5375 6.225 7.5125 6.775 6.1375C6.775 6.1375 7.6125 5.875 9.525 7.1625C10.325 6.9375 11.175 6.825 12.025 6.825C12.875 6.825 13.725 6.9375 14.525 7.1625C16.4375 5.8625 17.275 6.1375 17.275 6.1375C17.825 7.5125 17.475 8.5375 17.375 8.7875C18.0125 9.4875 18.4 10.375 18.4 11.475C18.4 15.3125 16.0625 16.1625 13.8375 16.4125C14.2 16.725 14.5125 17.325 14.5125 18.2625C14.5125 19.6 14.5 20.675 14.5 21.0125C14.5 21.275 14.6875 21.5875 15.1875 21.4875C19.1375 20.1625 22 16.4125 22 12C22 6.475 17.525 2 12 2Z" fill="currentColor"/>
                </svg>
                <span class="text-xs">Khepridev</span>
              </a>
            </div>

          </div>
        </div>
      </transition>

      <!-- Buttons Skeleton -->
      <div v-if="isButtonsLoading" class="absolute bottom-4 left-2 right-2 flex items-center justify-between">
        <!-- Boş alan (Download Progress yerine) -->
        <div class="w-[620px]"></div>

        <!-- Boş alan -->
        <div class="flex-1"></div>

        <!-- Settings ve Start Button Skeleton -->
        <div class="flex gap-2">
          <div class="w-10 h-10 bg-white/5 rounded animate-pulse"></div>
          <div class="w-24 h-10 bg-white/5 rounded animate-pulse"></div>
        </div>
      </div>

      <!-- Actual Buttons -->
      <div v-else class="absolute bottom-4 left-2 right-2 flex items-center justify-between">
        <!-- Download Progress -->
        <div v-if="isDownloading" class="w-[620px]">
          <div class="flex flex-col gap-1 bg-black/40 p-2 rounded-lg">
            <!-- Progress Bar -->
            <div class="h-2 bg-white/10 rounded-full overflow-hidden">
              <div 
                class="h-full bg-blue-500 transition-all duration-300"
                :style="{ width: `${downloadProgress}%` }"
              ></div>
            </div>
            <!-- Download Info -->
            <div class="flex justify-between text-[10px] text-white/70">
              <span class="truncate max-w-[250px]">{{ currentFile }}</span>
              <span>{{ formatSize(downloadedSize) }} / {{ formatSize(totalSize) }} ({{ downloadProgress }}%)</span>
            </div>
          </div>
        </div>

        <!-- Boş alan -->
        <div class="flex-1"></div>

        <!-- Settings Button -->
        <button 
          @click="() => { currentPage = 'settings'; restoreAudioState(); }"
          class="px-3 py-2 rounded transition-all duration-300 mr-2 group"
          :class="{
            'bg-blue-500/50 hover:bg-blue-500/20': currentPage === 'settings',
            'bg-blue-500/20 hover:bg-blue-500/50': currentPage !== 'settings'
          }"
        >
          <svg class="w-5 h-5 text-white transition-transform duration-300 group-hover:rotate-[30deg]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </button>

        <!-- Apps Button -->
        <button 
          @click="() => { currentPage = 'apps'; restoreAudioState(); }"
          class="relative px-3 py-2 rounded transition-all duration-300 mr-2 group"
          :class="{
            'bg-green-500/50 hover:bg-green-500/20': currentPage === 'apps',
            'bg-green-500/20 hover:bg-green-500/50': currentPage !== 'apps'
          }"
        >
          <svg class="w-5 h-5 text-white transition-transform duration-300 group-hover:scale-110 group-hover:rotate-[15deg]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
          </svg>
          
          <!-- Notification Indicator - Kırmızı nokta -->
          <div v-if="hasAppUpdates" 
               class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full pulsating-dot">
          </div>
        </button>
      </div>
    </div>
  </div>

  <!-- Apps Sayfası -->
  <div v-else-if="currentPage === 'apps'" class="relative flex-1 page-margin">
    <div class="glass-effect rounded-2xl mt-2">          
      <!-- Apps Header -->
      
      <!-- Apps List Container -->
      <div class="max-h-[380px] overflow-y-auto scrollbar-custom p-4">
        
        <div class="space-y-3">
          <div v-for="app in manifest?.apps" 
               :key="app.id"
               class="flex items-center gap-3 h-[52px] px-4 rounded-[6px] bg-black/60 backdrop-blur-sm border border-[#232323]/50 
                      transition-all duration-300 hover:bg-black/40">
            <!-- App Icon -->
            <img :src="app.icon" 
                 :alt="app.name"
                 class="w-7 h-7 rounded-[4px] object-cover" />
            
            <!-- App Info -->
            <div class="flex-1 flex items-center justify-between">
              <div class="flex flex-col">
                <span class="text-white/90 text-sm">{{ app.name }}</span>
                <span class="text-white/50 text-xs">{{ formatSize(calculateAppSize(app)) }}</span>
              </div>
              
              <!-- Version Badge -->
              <span class="text-[10px] px-2 py-0.5 rounded"
                    :class="{
                      'bg-gradient-to-r from-[#ffffff05] to-[#666666] text-white': app.version.startsWith('1'),
                      'bg-gradient-to-r from-[#3498db05] to-[#2980b9] text-white': app.version.startsWith('2'),
                      'bg-gradient-to-r from-[#e74c3c05] to-[#c0392b] text-white': app.version.startsWith('3'),
                      'bg-gradient-to-r from-[#9b59b605] to-[#8e44ad] text-white': app.version.startsWith('4'),
                      'bg-gradient-to-r from-[#f1c40f05] to-[#f39c12] text-white': app.version.startsWith('5'),
                      'bg-gradient-to-r from-[#2ecc7105] to-[#27ae60] text-white': app.version.startsWith('6'),
                      'bg-gradient-to-r from-[#1abc9c05] to-[#16a085] text-white': app.version.startsWith('7')
                    }">
                    v{{ app.version }}
                  </span>

              <!-- Download/Update/Start Button -->
              <button 
                @click="handleAppAction(app)"
                :class="{
                  'px-2 py-1 rounded transition-all duration-300 text-xs font-medium': true,
                  'bg-green-500/30 hover:bg-green-500/40 text-white/90': !isDownloading && getButtonTextForApp(app) === t('download'),
                  'bg-yellow-500/30 hover:bg-yellow-500/40 text-white/90': !isDownloading && getButtonTextForApp(app) === t('update'),
                  'bg-blue-500/30 hover:bg-blue-500/40 text-white/90': !isDownloading && getButtonTextForApp(app) === t('start'),
            'bg-black/40 text-white/70 cursor-wait': isDownloading
          }"
        >
          <span v-if="isDownloading">{{ t('downloading') }}</span>
                <span v-else>{{ getButtonTextForApp(app) }}</span>
        </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Settings Sayfası -->
  <div v-else-if="currentPage === 'settings'" class="relative flex-1 p-4 page-margin">
    <div class="bg-black/40  rounded-lg p-4 max-h-[380px] overflow-y-auto settings-scrollbar">
      
      <!-- Kurulum Yolu -->
      <div class="mb-4">
        <label class="block text-white mb-2 select-none flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
          </svg>
          {{ t('installPath') }}
        </label>
        <div class="flex gap-2">
          <input type="text" 
                 v-model="installPath" 
                 class="flex-1 bg-black/30 border border-white/10 rounded-lg px-2 py-1 text-white" 
                 readonly />
          <button @click="selectInstallPath" 
                  class="px-4 py-2 rounded-lg bg-blue-500/20 hover:bg-blue-500/50 text-white transition-all select-none">
            {{ t('select') }}
          </button>
        </div>
      </div>

      <!-- Bildirim Ayarları -->
      <div class="space-y-4">
        <!-- Genel Bildirimler -->
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-white font-medium mb-1 select-none flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
              </svg>
              {{ t('notifications') }}
            </h3>
            <p class="text-white/50 text-sm select-none ml-7">
              {{ notificationsEnabled ? t('notificationsEnabled') : t('notificationsDisabled') }}
            </p>
          </div>
          <button @click="toggleNotifications" 
                  class="w-12 h-6 rounded-full relative transition-colors select-none"
                  :class="notificationsEnabled ? 'bg-blue-500/30' : 'bg-white/10'">
            <div class="w-5 h-5 rounded-full bg-white absolute top-0.5 transition-transform"
                 :class="notificationsEnabled ? 'translate-x-6' : 'translate-x-0.5'">
            </div>
          </button>
        </div>

        <!-- Haber Bildirimleri -->
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-white font-medium mb-1 select-none flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
              </svg>
              {{ t('newsNotifications') }}
            </h3>
            <p class="text-white/50 text-sm select-none ml-7">
              {{ newsNotificationsEnabled ? t('newsNotificationsEnabled') : t('newsNotificationsDisabled') }}
            </p>
          </div>
          <button @click="toggleNewsNotifications" 
                  class="w-12 h-6 rounded-full relative transition-colors select-none"
                  :class="newsNotificationsEnabled ? 'bg-blue-500/30' : 'bg-white/10'">
            <div class="w-5 h-5 rounded-full bg-white absolute top-0.5 transition-transform"
                 :class="newsNotificationsEnabled ? 'translate-x-6' : 'translate-x-0.5'">
            </div>
          </button>
        </div>

        <!-- Güncelleme Bildirimleri -->
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-white font-medium mb-1 select-none flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              {{ t('updateNotifications') }}
            </h3>
            <p class="text-white/50 text-sm select-none ml-7">
              {{ updateNotificationsEnabled ? t('updateNotificationsEnabled') : t('updateNotificationsDisabled') }}
            </p>
          </div>
        <button 
            @click="toggleUpdateNotifications"
            class="w-12 h-6 rounded-full relative transition-colors duration-200"
            :class="updateNotificationsEnabled ? 'bg-blue-500/30' : 'bg-white/10'"
          >
            <div
              class="w-5 h-5 rounded-full bg-white absolute top-0.5 transition-transform duration-200"
              :class="updateNotificationsEnabled ? 'translate-x-6' : 'translate-x-0.5'"
            ></div>
        </button>
        </div>

      </div>

    </div>
  </div>

  <!-- Custom Notification -->
  <div v-if="notification.show" 
       class="fixed bottom-4 right-4 p-3 bg-black/80 border border-white/10 rounded-lg shadow-lg transition-all duration-300 z-50"
       :class="{'translate-y-0 opacity-100': notification.visible, 'translate-y-4 opacity-0': !notification.visible}">
    <div class="flex items-center gap-3">
      <!-- Notification Icon -->
      <div :class="notification.type === 'success' ? 'text-green-400' : 'text-blue-400'">
        <svg v-if="notification.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <!-- Notification Message -->
      <div class="text-white text-sm">{{ notification.message }}</div>
    </div>
  </div>

  <!-- Arka plan için Skeleton -->
</template>

<script>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
const electron = window.require('electron')
const { ipcRenderer } = electron
const path = window.require('path')
const fs = window.require('fs')
const Store = require('electron-store')
const store = new Store()

export default {
  setup() {
    const isLoading = ref(true)
    const isButtonsLoading = ref(true)
    const connectionError = ref(false)
    const importantNotice = ref(null)
    const isDownloading = ref(false)
    const downloadProgress = ref(0)
    const news = ref([])
    const background = ref({
      mode: 2,
      imageUrl: '',
      videoUrl: ''
    })
    const isDragging = ref(false)
    const dragOffset = ref({ x: 0, y: 0 })
    const currentFile = ref('')
    const downloadedSize = ref(0)
    const totalSize = ref(0)
    const newsContainer = ref(null)
    const currentScrollIndex = ref(0)
    const isAtEnd = ref(false)
    const autoScrollInterval = ref(null)
    const autoScrollDelay = ref(null)
    const socialMediaItems = ref([])
    const currentLang = ref(store.get('language') || 'en')
    const translations = ref({})
    const showLangMenu = ref(false)
    const isDraggingNews = ref(false)
    const dragStartX = ref(0)
    const dragStartScrollLeft = ref(0)
    const canScrollLeft = ref(false)
    const canScrollRight = ref(true)
    const currentPage = ref('home')
    const installPath = ref(store.get('installPath') || '')
    const notificationsEnabled = ref(store.get('notificationsEnabled', true))
    const newsNotificationsEnabled = ref(store.get('newsNotificationsEnabled', true))
    const manifest = ref(null)
    const videoPlayer = ref(null)
    const updateNotificationsEnabled = ref(store.get('updateNotificationsEnabled', true))
    const isPageReady = ref(false)
    const versionFilter = ref('all')
    const statusFilter = ref('all')
    const isVersionDropdownOpen = ref(false)
    const isStatusDropdownOpen = ref(false)
    // Sanal makine kontrolü
    const isVirtualMachine = ref(store.get('isVirtualMachine', false))
    
    // Dil menüsü için computed
    const availableLanguages = computed(() => {
      const defaultLanguages = [
        { code: 'tr', name: 'Türkçe' },
        { code: 'en', name: 'English' }
      ]
      
      // Manifest'ten ek dilleri al
      const manifestLanguages = manifest.value?.languages || {}
      const additionalLanguages = Object.entries(manifestLanguages).map(([code, name]) => ({
        code,
        name
      }))
      
      // Varsayılan diller ve manifest'ten gelen dilleri birleştir
      return [...defaultLanguages, ...additionalLanguages]
    })

    // Çevirileri al
    const t = (key) => {
      if (!manifest.value?.translations || !currentLang.value) return key
      return manifest.value.translations[currentLang.value]?.[key] || key
    }

    // Mevcut dile göre duyuruyu getir
    const currentNotice = computed(() => {
      if (!translations.value[currentLang.value]) return null
      const notices = translations.value[currentLang.value].notices
      return notices && notices.length > 0 ? notices[0] : null
    })

    // Dil değiştirme fonksiyonu
    const changeLang = async (lang) => {
      try {
        await ipcRenderer.invoke('change-language', lang)
        currentLang.value = lang
        showLangMenu.value = false
        
        // Menüyü güncelle
        if (manifest.value?.translations?.[lang]) {
          translations.value = manifest.value.translations
        }
        
        // Dil değiştiğinde app güncellemelerini kontrol et ve UI'ı güncelle
        await checkAppsForUpdates()
        
        // Apps sayfasındaysa butonları güncelle
        if (currentPage.value === 'apps' && manifest.value?.apps) {
          nextTick(() => {
            // Buton metinlerini güncellemek için tüm appları yeniden işle
            for (const app of manifest.value.apps) {
              getButtonTextForApp(app)
            }
          })
        }
      } catch (error) {
        console.error('Dil değiştirme hatası:', error)
      }
    }

    // Video önbelleğe alma fonksiyonu
    const preloadVideo = (url) => {
      return new Promise((resolve, reject) => {
        const video = document.createElement('video')
        video.preload = 'auto'
        video.autoplay = false
        video.src = url

        video.onloadeddata = () => {
          resolve()
        }
        video.onerror = () => {
          reject()
        }
      })
    }

    // Computed property olarak menuItems
    const menuItems = computed(() => {
      if (!manifest.value?.translations || !currentLang.value) return []
      return manifest.value.translations[currentLang.value]?.menuItems || []
    })

    // Logo font stilini yönet
    const getLogoFontStyle = computed(() => {
      if (manifest.value?.logo?.text?.style?.type === 'font-link') {
        // Font linkini dinamik olarak ekle
        const link = document.createElement('link')
        link.rel = 'stylesheet'
        link.href = manifest.value.logo.text.style.value
        document.head.appendChild(link)
        
        // Font ailesini URL'den çıkar
        const fontFamily = manifest.value.logo.text.style.value
          .split('family=')[1]
          .split(':')[0]
          .replace('+', ' ')
        
        return {
          fontFamily: fontFamily
        }
      }
      return {}
    })

    // Font ve CSS yönetimi
    watch(() => manifest.value?.logo?.text?.style, (newStyle) => {
      if (newStyle) {
        // Font Link kontrolü
        if (newStyle.fontLink?.enabled) {
          const oldFontLink = document.querySelector('link[data-logo-font]')
          if (oldFontLink) oldFontLink.remove()
          
          const link = document.createElement('link')
          link.rel = 'stylesheet'
          link.href = newStyle.fontLink.value
          link.setAttribute('data-logo-font', '')
          document.head.appendChild(link)
        }
        
        // CSS Link kontrolü
        if (newStyle.cssLink) {
          const oldCssLink = document.querySelector('link[data-logo-style]')
          if (oldCssLink) oldCssLink.remove()
          
          const link = document.createElement('link')
          link.rel = 'stylesheet'
          link.href = newStyle.cssLink.value
          link.setAttribute('data-logo-style', '')
          document.head.appendChild(link)
          
          // Özel CSS kuralları
          if (newStyle.cssLink.customCSS) {
            const oldStyle = document.querySelector('style[data-logo-custom]')
            if (oldStyle) oldStyle.remove()
            
            const style = document.createElement('style')
            style.textContent = newStyle.cssLink.customCSS
            style.setAttribute('data-logo-custom', '')
            document.head.appendChild(style)
          }
        }
      }
    }, { immediate: true })
    
    // App güncellemelerini kontrol et
    const hasAppUpdates = computed(() => {
      // Eğer güncelleme bildirimleri kapalıysa kırmızı noktayı gösterme
      if (!updateNotificationsEnabled.value) {
        return false;
      }
      
      if (!manifest.value?.apps) return false;
      
      // Gerçekten güncelleme gerektiren veya yeni olan app var mı kontrol edelim
      const hasAnyUpdates = manifest.value.apps.some(app => {
        // Eğer needsUpdate true ise ve uygulamanın exe dosyası varsa göster
        if (app.needsUpdate === true) {
          console.log(`App ID: ${app.id} güncelleme gerektiriyor - kırmızı nokta gösteriliyor`);
          return true;
        }
        
        // Eğer isNew özelliği varsa ve true ise göster
        if (app.isNew === true) {
          console.log(`App ID: ${app.id} yeni bir uygulama - kırmızı nokta gösteriliyor`);
          return true;
        }
        
        // Hiçbir koşul sağlanmazsa false
        return false;
      });
      
      console.log(`Kırmızı nokta durumu: ${hasAnyUpdates}`);
      return hasAnyUpdates;
    })
    
    // Tüm uygulamaların güncelleme durumunu kontrol eden fonksiyon
    const checkAppsForUpdates = async () => {
      try {
        // Manifest veya yükleme yolu yoksa işlem yapma
        if (!manifest.value?.apps || !installPath.value) {
          console.log('Manifest veya yükleme yolu henüz hazır değil, güncelleme kontrolü atlanıyor')
          return 0
        }
        
        console.log('Tüm uygulamalar için güncelleme durumu kontrol ediliyor')
        let updatedAppsCount = 0
        
        // Son bilinen uygulama ID'lerini al
        const lastKnownAppIds = store.get('knownAppIds', [])
        
        // Yeni uygulama ID'lerini kontrol et
        const currentAppIds = manifest.value.apps.map(app => app.id)
        
        // Yeni eklenen uygulamaları bul
        const newAppIds = currentAppIds.filter(id => !lastKnownAppIds.includes(id))
        
        // Bilinen app ID'leri güncelle
        store.set('knownAppIds', currentAppIds)
        
        // Store'dan kaydedilmiş yeni app ID'lerini al
        const storedNewAppIds = store.get('newAppIds', [])
        
        // Yeni app ID'leri sakla - sadece gerçekten ilk kez eklenen ID'leri ekle
        if (newAppIds.length > 0) {
          // Var olan ve yeni ID'leri birleştir, tekrar edenleri filtrele
          const updatedNewAppIds = [...new Set([...storedNewAppIds, ...newAppIds])]
          console.log('Güncellenmiş newAppIds:', updatedNewAppIds)
          store.set('newAppIds', updatedNewAppIds)

          // Yeni app varsa kırmızı nokta göster için bir flag ayarla
          if (newAppIds.length > 0) {
            console.log('YENİ UYGULAMALAR VAR: Kırmızı nokta gösterilecek')
            updatedAppsCount += newAppIds.length
          }
        }
        
        // İndirilmiş uygulamaların ID'lerini al
        const downloadedAppIds = store.get('downloadedAppIds', [])
        
        // Her uygulama için güncelleme kontrolü yap
        for (const app of manifest.value.apps) {
          try {
            if (!app || !app.id || !app.files || !Array.isArray(app.files) || app.files.length === 0) {
              continue // Geçersiz app bilgisi veya dosya listesi yoksa atla
            }
            
            // Uygulama daha önce indirildi mi kontrol et
            const isDownloaded = downloadedAppIds.includes(app.id)
            
            // Yeni eklenen uygulama ise (daha önce indirilmemişse) işaretle
            // Store'daki newAppIds listesinde varsa bu uygulama yeni demektir
            const isNewApp = storedNewAppIds.includes(app.id)
            
            // isNew ve needsUpdate durumunu reaktif olarak güncelle
            app.isNew = isNewApp && !isDownloaded
            
            // Eğer yeni bir uygulama ise, sayacı artır
            if (app.isNew) {
              console.log(`App ID: ${app.id} (${app.name}) YENİ UYGULAMA`);
              updatedAppsCount++;
            }
            
            console.log(`App ID: ${app.id} (${app.name}) için kontrol: Yeni mi: ${app.isNew ? 'Evet' : 'Hayır'}, İndirildi mi: ${isDownloaded ? 'Evet' : 'Hayır'}`)
            
            // Tüm dosyaların varlığını kontrol et
            let allFilesExist = true
            let needsUpdate = false
            
            // Ana exe dosyası
            const exeFile = app.files.find(f => f.path.toLowerCase().endsWith('.exe'))
            if (!exeFile) {
              console.log(`App ID: ${app.id} için exe dosyası bulunamadı, indirmek gerekiyor`)
              app.needsUpdate = false // dosya yok, indirme göster
              continue
            }
            
            // Exe dosyasının var olup olmadığını kontrol et
            const exePath = path.join(installPath.value, exeFile.path)
            
            // Exe dosyası varsa = bu uygulama yüklü demektir
            // Yüklü değilse indir butonu göster, needsUpdate = false
            if (!fs.existsSync(exePath)) {
              console.log(`App ID: ${app.id} için exe dosyası bulunamadı: ${exePath}`)
              app.needsUpdate = false
              continue
            }
            
            // Exe dosyası var - app yüklü demektir
            // App ID'sini indirilenler listesine ekle
            if (!downloadedAppIds.includes(app.id)) {
              downloadedAppIds.push(app.id)
              store.set('downloadedAppIds', downloadedAppIds)
              console.log(`App ID: ${app.id} indirilmiş uygulamalar listesine eklendi`)
            }
            
            // Şimdi app'in güncelleme gerektirip gerektirmediğini kontrol et
            // Tüm dosyaları kontrol et
            for (const file of app.files) {
              if (!file.path || !file.hash) continue
              
              const filePath = path.join(installPath.value, file.path)
              const fileExists = fs.existsSync(filePath)
              
              if (!fileExists) {
                // Dosya yok - güncelleme gerekir
                allFilesExist = false
                needsUpdate = true
                break
              }
              
              // Dosya var - boyut ve hash kontrolü yap
              try {
                // Dosya hash kontrolü
                const fileValid = await ipcRenderer.invoke('check-file', {
                  path: filePath,
                  size: file.size,
                  hash: file.hash
                })
                
                if (!fileValid) {
                  // Hash uyuşmazlığı - güncelleme gerekir
                  needsUpdate = true
                  break
                }
              } catch (error) {
                console.error(`Hash hesaplama hatası (${file.path}):`, error)
                // Hata durumunda güvenli yol - güncelleme gerekir
                needsUpdate = true
                break
              }
            }
            
            // Kesin güncelleme gerektiriyor mu?
            app.needsUpdate = needsUpdate
            
            if (needsUpdate) {
              console.log(`App ID: ${app.id} GÜNCELLEME GEREKTİRİYOR`);
              updatedAppsCount++;
            }
            
            // Durumu logla
            console.log(`App ID: ${app.id} durumu: 
              Tüm dosyalar var mı: ${allFilesExist ? 'Evet' : 'Hayır'}
              Güncelleme gerekli: ${needsUpdate ? 'Evet' : 'Hayır'}
              needsUpdate flag: ${app.needsUpdate ? 'Evet' : 'Hayır'}
              isNew flag: ${app.isNew ? 'Evet' : 'Hayır'}
            `)
          } catch (error) {
            console.error(`App kontrolü hatası (${app?.id}):`, error)
          }
        }
        
        console.log(`Toplam güncelleme/yeni uygulama sayısı: ${updatedAppsCount}`)
        return updatedAppsCount
      } catch (error) {
        console.error('App güncelleme kontrolü hatası:', error)
        return 0
      }
    }
    
    // Manifest veya installPath değiştiğinde app güncellemelerini kontrol et
    watch([manifest, installPath], async () => {
      await checkAppsForUpdates()
    }, { immediate: true })
    
    // Apps güncellemelerini dinle
    onMounted(() => {
      ipcRenderer.on('apps-updated', async (event, updatedApps) => {
        if (manifest.value) {
          manifest.value.apps = updatedApps
          await checkAppsForUpdates()
        }
      })
      
      // App dosya güncellemeleri için dinlemeyi ekle
      ipcRenderer.on('app-files-updated', async (event, updatedFiles) => {
        console.log('App dosya güncellemeleri alındı:', updatedFiles.length)
        await checkAppsForUpdates()
      })
    })
    
    // Component unmounted olduğunda listener'ı temizle
    onUnmounted(() => {
      // Event dinleyicilerini temizle
      ipcRenderer.removeAllListeners('manifest-updated')
      ipcRenderer.removeAllListeners('files-updated')
      ipcRenderer.removeAllListeners('app-files-updated')
      ipcRenderer.removeAllListeners('apps-updated')
      ipcRenderer.removeAllListeners('download-progress')
      ipcRenderer.removeAllListeners('show-apps-page')
      ipcRenderer.removeAllListeners('show-news-notifications')
      ipcRenderer.removeAllListeners('show-update-notification')
      ipcRenderer.removeAllListeners('news-updated')
      ipcRenderer.removeAllListeners('social-media-updated')
      ipcRenderer.removeAllListeners('background-updated')
      ipcRenderer.removeAllListeners('notice-updated')
      ipcRenderer.removeAllListeners('menu-updated')
      ipcRenderer.removeAllListeners('update-settings')
      
      window.removeEventListener('message', () => {})
      if (newsContainer.value) {
        newsContainer.value.removeEventListener('scroll', checkScrollPosition)
      }
    })

    // Manifest kontrolünü güncelleyelim
    const loadManifest = async () => {
      try {
        isLoading.value = true
        connectionError.value = false
        isPageReady.value = false
        isButtonsLoading.value = true

        // Manifest'i yükle
        const response = await ipcRenderer.invoke('get-manifest')
        
        if (!response) {
          throw new Error('Manifest yüklenemedi')
        }

        manifest.value = response

        // Diğer verileri yükle
        if (response.news) {
          news.value = response.news
        }
        
        if (response.socialMedia) {
          socialMediaItems.value = response.socialMedia
        }

        if (response.background) {
          background.value = response.background
        }

        // Sayfa hazır
        isPageReady.value = true
        
        // Button loading'i daha sonra kapat
        setTimeout(() => {
          isButtonsLoading.value = false
        }, 700)

      } catch (error) {
        console.error('Manifest yükleme hatası:', error)
        connectionError.value = true
        isButtonsLoading.value = false
        isPageReady.value = false
      } finally {
        isLoading.value = false
      }
    }

    // Component mounted
    onMounted(() => {
      loadManifest()
      
      // Sayfa yenilendikten sonra Apps sayfasına gitme kontrolü
      if (localStorage.getItem('redirect_to_apps') === 'true') {
        localStorage.removeItem('redirect_to_apps')
        currentPage.value = 'apps'
      }
      
      // Haber güncellemelerini dinle
      ipcRenderer.on('news-updated', (event, updatedNews) => {
        console.log('Haberler güncellendi:', updatedNews)
        news.value = updatedNews
      })

      // Manifest güncellemelerini dinle
      ipcRenderer.on('manifest-updated', (event, updatedManifest) => {
        console.log('Manifest güncellendi')
        manifest.value = updatedManifest
        if (updatedManifest.news) {
          news.value = updatedManifest.news
        }
      })
      
      // Kalıcı olarak saklanan newAppIds'i geri yükle
      const storedNewAppIds = store.get('newAppIds', [])
      // Manifeste newAppIds'i işaretle
      if (manifest.value?.apps && storedNewAppIds.length > 0) {
        manifest.value.apps.forEach(app => {
          if (storedNewAppIds.includes(app.id)) {
            app.isNew = true
          }
        })
      }
    })

    // Manifest ve güncelleme dinleyicilerini ekleyelim
    onMounted(() => {
      // Sanal makine kontrolü kaldırıldı

      // Haber güncellemeleri
      ipcRenderer.on('news-updated', (event, updatedNews) => {
        news.value = updatedNews
      })

      // Sosyal medya güncellemeleri
      ipcRenderer.on('social-media-updated', (event, updatedSocialMedia) => {
        socialMediaItems.value = updatedSocialMedia
      })

      // Arka plan güncellemeleri
      ipcRenderer.on('background-updated', (event, updatedBackground) => {
        background.value = updatedBackground
        // Video elementi varsa ve video url değiştiyse, videoyu yeniden yükle
        if (background.value.mode === 2 && videoPlayer.value) {
          videoPlayer.value.load()
        }
      })

      // Duyuru güncellemeleri
      ipcRenderer.on('notice-updated', (event, updatedNotice) => {
        if (manifest.value) {
          manifest.value.importantNotice = updatedNotice
        }
      })

      // Menü güncellemeleri
      ipcRenderer.on('menu-updated', async (event, { translations: newTranslations, currentLang }) => {
        translations.value = newTranslations
        if (currentLang) {
          currentLang.value = currentLang
        }
        
        // Menü güncellemesinden sonra app güncellemelerini kontrol et ve UI'ı güncelle
        await checkAppsForUpdates()
        
        // Apps sayfasındaysa butonları güncelle
        if (currentPage.value === 'apps' && manifest.value?.apps) {
          nextTick(() => {
            // Buton metinlerini güncellemek için tüm appları yeniden işle
            for (const app of manifest.value.apps) {
              getButtonTextForApp(app)
            }
          })
        }
      })
    })

    // Component unmounted
    onUnmounted(() => {
      ipcRenderer.removeAllListeners('news-updated')
      ipcRenderer.removeAllListeners('manifest-updated')
      ipcRenderer.removeAllListeners('social-media-updated')
      ipcRenderer.removeAllListeners('background-updated')
      ipcRenderer.removeAllListeners('notice-updated')
      ipcRenderer.removeAllListeners('menu-updated')
    })

    const startDrag = (e) => {
      // Sadece draggable class'ına sahip elementten sürükleme yapılabilsin
      if (e.target.classList.contains('draggable')) {
        isDragging.value = true
        dragOffset.value = {
          x: e.clientX,
          y: e.clientY
        }
      }
    }

    const onDrag = (e) => {
      if (isDragging.value) {
        ipcRenderer.send('move-window', {
          x: e.screenX - dragOffset.value.x,
          y: e.screenY - dragOffset.value.y
        })
      }
    }

    const stopDrag = () => {
      isDragging.value = false
    }

    // Global event listener'ları ekle
    onMounted(() => {
      document.addEventListener('mousemove', onDrag)
      document.addEventListener('mouseup', stopDrag)
    })

    // Temizlik
    onUnmounted(() => {
      document.removeEventListener('mousemove', onDrag)
      document.removeEventListener('mouseup', stopDrag)
    })

    const openSettings = () => {
      ipcRenderer.send('open-settings')
    }

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('tr-TR', {
        day: 'numeric',
        month: 'long'
      })
    }

    const minimizeWindow = () => {
      ipcRenderer.send('minimize-window')
    }

    const closeWindow = () => {
      ipcRenderer.send('close-window')
    }

    const formatSize = (bytes) => {
      if (bytes === 0) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`
    }

    // Bir app'in toplam boyutunu hesaplama fonksiyonu
    const calculateAppSize = (app) => {
      if (!app || !app.files || !Array.isArray(app.files)) return 0
      
      // Tüm dosyaların boyutlarını topla
      return app.files.reduce((total, file) => {
        return total + (file.size || 0)
      }, 0)
    }

    const allFilesExist = ref(false)

    // Update the checkFiles function to handle apps directory with id and name
    const checkFiles = () => {
      const manifestFiles = manifest.value?.files || []
      const installPath = store.get('installPath')
      
      if (!installPath || !manifestFiles.length) {
        console.log('Dosya kontrolü için path veya manifest yok')
        return
      }
      
      console.log(`${manifestFiles.length} dosya kontrol ediliyor...`)
      
      // Tüm dosyaların kontrolü
      Promise.all(
        manifestFiles.map(async (file) => {
          try {
            const filePath = path.join(installPath, file.path)
            // File mevcutluğunu, boyutunu ve hash'ini kontrol et
            const fileOK = await ipcRenderer.invoke('check-file', {
              path: filePath,
              size: file.size,
              hash: file.hash
            })
            return { ...file, exists: fileOK }
          } catch (error) {
            console.error(`Dosya kontrolü hatası (${file.path}):`, error)
            return { ...file, exists: false }
          }
        })
      ).then((results) => {
        // Tüm dosya sonuçlarını topla
        files.value = results
        
        // Güncellenecek dosyaları filtrele
        updatedFiles.value = results.filter(file => !file.exists)
        
        // Güncelleme butonunu kontrol et
        needsUpdate.value = updatedFiles.value.length > 0
        
        console.log(`Toplam ${files.value.length} dosya, ${updatedFiles.value.length} güncelleme gerekiyor`)
        console.log('Güncelleme durumu:', needsUpdate.value)
        
        // Yükleme durumunu güncelle
        isLoading.value = false
      })
    }

    // Manifest ve dosya güncelleme mantığını iyileştir
    onMounted(() => {
      // files-updated olayını dinle
      ipcRenderer.on('files-updated', (event, updatedFileList) => {
        console.log('Files-updated olayı alındı, dosya sayısı:', updatedFileList.length)
        // Dosya güncellemelerini hemen kontrol et
        checkFiles()
      })
      
      // Manifest güncellemelerini dinle ve dosyaları kontrol et
      ipcRenderer.on('manifest-updated', (event, updatedManifest) => {
        console.log('Manifest güncellendi, versiyon:', updatedManifest.version)
        
        // Yeni manifesti sakla
        manifest.value = updatedManifest
        
        // Dosya güncellemelerini hemen kontrol et
        console.log('Dosya güncellemeleri kontrol ediliyor...')
        checkFiles()
        
        // Diğer manifest öğelerini güncelle
        if (updatedManifest.news) {
          news.value = updatedManifest.news
        }
        
        if (updatedManifest.importantNotice) {
          importantNotice.value = updatedManifest.importantNotice
        }
        
        if (updatedManifest.background) {
          background.value = updatedManifest.background
        }
        
        if (updatedManifest.logo) {
          logo.value = updatedManifest.logo
        }
        
        if (updatedManifest.settings && updatedManifest.settings.version) {
          appVersion.value = updatedManifest.settings.version
        }
        
        if (updatedManifest.socialMedia) {
          socialMediaItems.value = updatedManifest.socialMedia
        }
        
        console.log('Manifest değerleri güncellendi')
      })
    })

    // Buton metni için ref ve computed ekleyelim
    const buttonText = ref('download')

    // Buton durumunu kontrol eden fonksiyon
    const checkAppStatus = async (app) => {
      if (!app || !app.id) {
        buttonText.value = t('download')
        return
      }
      
      if (!installPath.value) {
        buttonText.value = t('download')
        return
      }
      
      const mainFilePath = path.join(installPath.value, app.mainPath)
      
      // Ana dosya var mı kontrol et
      if (!fs.existsSync(mainFilePath)) {
        buttonText.value = t('download')
        return
      }
      
      // Ana dosya ve diğer dosyaların kontrolü
      let allFilesValid = true
      
      try {
        // Ana dosya kontrolü
        const mainFileStatus = await ipcRenderer.invoke('check-file', {
          path: mainFilePath,
          size: app.files[0].size,
          hash: app.files[0].hash
        })
        
        if (!mainFileStatus) {
          allFilesValid = false
        }
        
        // Diğer dosyaların kontrolü
        if (allFilesValid) {
          for (const file of app.files) {
        const filePath = path.join(installPath.value, file.path)
            if (!fs.existsSync(filePath)) {
              allFilesValid = false
              break
            }
            
            const fileStatus = await ipcRenderer.invoke('check-file', {
          path: filePath,
          size: file.size,
          hash: file.hash
        })
            
            if (!fileStatus) {
              allFilesValid = false
              break
            }
          }
        }
      } catch (error) {
        console.error(`App ID ${app.id} dosya kontrolü hatası:`, error)
        allFilesValid = false
      }
      
      // Uygulama için buton metni güncelleniyor
      buttonText.value = allFilesValid ? t('start') : t('update')
      
      // App'in güncelleme durumunu da güncelle
      app.needsUpdate = !allFilesValid
      
      console.log(`App ID: ${app.id}, Buton metni: ${buttonText.value}, Güncelleme gerekli: ${app.needsUpdate}`)
      return buttonText.value
    }

    // Watch ile app değişikliklerini izle
    watch(() => manifest.value?.apps, async () => {
      if (manifest.value?.apps?.[0]) {
        await checkAppStatus(manifest.value.apps[0])
      }
    }, { immediate: true })

    // Dil değişikliğinde buttonText'i güncelle
    watch(() => currentLang.value, async () => {
      console.log('Dil değişti, buton metni güncelleniyor...')
      if (manifest.value?.apps?.[0]) {
        await checkAppStatus(manifest.value.apps[0])
      }
    })

    // App işlemlerini yöneten fonksiyon
    const handleAppAction = async (app) => {
      try {
        if (!installPath.value) {
          alert(t('selectInstallPath'))
          return
        }

        // App ID'sini kontrol et
        if (!app || !app.id) {
          console.error('Geçersiz app ID')
          return
        }

        console.log(`App işlemi başlatılıyor, ID: ${app.id}, Name: ${app.name}`)
        
        // Bu app için buton metnini belirle
        const appButtonText = getButtonTextForApp(app)
        console.log(`App ${app.id}: Buton metni = ${appButtonText}`)
        
        // Buton işlevine göre ne yapılacağına karar ver
        if (appButtonText === t('download') || appButtonText === t('update')) {
          // İndirme veya güncelleme işlemi başlat
        const filesToDownload = []
          
          // İndirme listesini oluştur
          for (const file of app.files) {
          const filePath = path.join(installPath.value, file.path)
            const fileDir = path.dirname(filePath)
            
            // Dosya dizinini oluştur
            if (!fs.existsSync(fileDir)) {
              fs.mkdirSync(fileDir, { recursive: true })
            }
            
            filesToDownload.push({
              url: file.url,
            path: filePath,
            hash: file.hash
          })
        }

          // İndirme işlemini başlat
          isDownloading.value = true
          downloadProgress.value = 0
          
          console.log(`App ID: ${app.id} için indirme başlatılıyor, dosya sayısı: ${filesToDownload.length}`)
          
          for (const file of filesToDownload) {
            currentFile.value = path.basename(file.path)
            
            try {
              await ipcRenderer.invoke('download-file', {
                url: file.url,
                path: file.path,
                hash: file.hash
              })
            } catch (error) {
              console.error(`App ID: ${app.id} indirme hatası:`, error)
              throw error
            }
          }

          // İndirme işlemi tamamlandı
          isDownloading.value = false
          currentFile.value = ''
          downloadProgress.value = 0
          
          // Yenileme sonrası Apps sayfasına git
          localStorage.setItem('redirect_to_apps', 'true')
          window.location.reload()
          
          // Uygulama güncelleme/indirme tamamlandı, bildirim durumlarını sıfırla
          console.log(`App ID: ${app.id} için indirme/güncelleme tamamlandı, bildirim durumları sıfırlanıyor`);
          
          // İndirme veya güncelleme sonrası tüm flag'leri temizle
          if (app) {
            // Direkt olarak güncel değerleri atama
            app.needsUpdate = false;
            app.isNew = false;
            
            // Store'dan yeni app ID'sini kaldır - bu önemli!
            const storedNewAppIds = store.get('newAppIds', []);
            if (storedNewAppIds.includes(app.id)) {
              const updatedNewAppIds = storedNewAppIds.filter(id => id !== app.id);
              store.set('newAppIds', updatedNewAppIds);
              console.log(`App ID: ${app.id} artık yeni değil olarak işaretlendi ve store'dan kaldırıldı`);
            }
            
            // İndirilmiş uygulamalar listesine ekle - kırmızı noktanın düzgün çalışması için kritik
            const downloadedAppIds = store.get('downloadedAppIds', []);
            if (!downloadedAppIds.includes(app.id)) {
              downloadedAppIds.push(app.id);
              store.set('downloadedAppIds', downloadedAppIds);
              console.log(`App ID: ${app.id} indirilmiş uygulamalar listesine eklendi`);
            }
            
            // Kırmızı noktanın hemen güncellenmesi için nextTick kullan
            nextTick(() => {
              // Bu asenkron olarak reactive değişkenlerin güncellenmesini ve yeniden render edilmesini sağlar
              console.log(`App ID: ${app.id} için nextTick çalıştı, UI güncelleniyor`);
              console.log(`Güncel kırmızı nokta durumu: ${hasAppUpdates.value}`);
            });
          }
          
          // Tüm app güncellemelerini yeniden kontrol et - bunu sonraya bırak
          await checkAppsForUpdates();
          
          // hasAppUpdates'in yeniden hesaplanmasını zorla (computed değerlerini reaktif olarak değiştirmek için)
          nextTick(() => {
            console.log('hasAppUpdates yeniden hesaplanıyor:', hasAppUpdates.value);
            
            // Manifest değişikliğini simüle ederek computed prop'un yeniden değerlendirilmesini sağla
            if (manifest.value && manifest.value.apps) {
              const tempApps = [...manifest.value.apps];
              manifest.value.apps = [];
              nextTick(() => {
                manifest.value.apps = tempApps;
              });
            }
          })
          
          // Uygulamanın başarıyla indirildiğini bildir
          showExternalNotification(t('notification'), `${app.name} ${appButtonText === t('download') ? t('downloadComplete') : t('updateComplete')}`)
        } else {
          // Başlatma işlemi - exe dosyasını bul
          let exePath = null
          
          // exe dosyasını bul
          const exeFile = app.files.find(f => f.path.toLowerCase().endsWith('.exe'))
          if (exeFile) {
            exePath = path.join(installPath.value, exeFile.path)
          } else {
            // Hiç exe dosyası bulunamazsa, mainPath'i dene
            if (app.mainPath) {
              if (app.mainPath.toLowerCase().endsWith('.exe')) {
                exePath = path.join(installPath.value, app.mainPath)
              } else {
                // mainPath bir dizin ise, app ile aynı isimde exe dosyasını dene
                exePath = path.join(installPath.value, app.mainPath, `${app.name}.exe`)
              }
            }
          }
          
          if (!exePath) {
            console.error(`App ID: ${app.id} için çalıştırılabilir dosya bulunamadı`)
            alert(`Hata: ${app.name} için çalıştırılabilir dosya bulunamadı!`)
          return
        }

          console.log(`App ID: ${app.id} başlatılıyor, path: ${exePath}`)
          
          // Dosyanın var olup olmadığını kontrol et
          if (!fs.existsSync(exePath)) {
            console.error(`Uygulama başlatma hatası: ${exePath} bulunamadı`)
            alert(`Uygulama başlatma hatası: Dosya bulunamadı: ${exePath}`)
            return
          }
          
          try {
            await ipcRenderer.invoke('start-client', exePath)
      } catch (error) {
            console.error(`App başlatma hatası: ${error.message}`)
            alert(`Uygulama başlatma hatası: ${error.message}`)
          }
        }
      } catch (error) {
        console.error(`App işlemi sırasında hata (ID: ${app?.id || 'bilinmiyor'}):`, error)
        isDownloading.value = false
        currentFile.value = ''
        downloadProgress.value = 0
        
        // Hata durumunda App'in güncelleme durumunu kontrol et
        if (app) {
          await checkAppsForUpdates()
          
          // Güncelleme sonuçlarını reaktif olarak dikkate al
          nextTick(() => {
            console.log('Hata sonrası hasAppUpdates durumu:', hasAppUpdates.value)
          })
        }
        
        alert(`Hata: ${error.message}`)
      }
    }

    // Progress event listener'ı ekleyelim
    onMounted(() => {
      ipcRenderer.on('download-progress', (event, { progress, downloaded }) => {
        downloadProgress.value = progress
        downloadedSize.value = downloaded
      })
    })

    // Component unmount olduğunda listener'ı temizleyelim
    onUnmounted(() => {
      ipcRenderer.removeAllListeners('download-progress')
    })

    const startAutoScroll = (direction) => {
      scrollNews(direction)
      
      autoScrollDelay.value = setTimeout(() => {
        autoScrollInterval.value = setInterval(() => {
          scrollNews(direction)
        }, 100)
      }, 300)
    }

    const stopAutoScroll = () => {
      if (autoScrollInterval.value) {
        clearInterval(autoScrollInterval.value)
        autoScrollInterval.value = null
      }
      if (autoScrollDelay.value) {
        clearTimeout(autoScrollDelay.value)
        autoScrollDelay.value = null
      }
    }

    // Component unmount olduğunda interval'ları temizle
    onUnmounted(() => {
      stopAutoScroll()
    })

    const scrollNews = (direction = 'right') => {
      if (!newsContainer.value) return
      
      const containerWidth = newsContainer.value.offsetWidth
      const scrollAmount = 168 * 2 // 2 kart kadar kaydır
      
      const newScrollLeft = direction === 'right' 
        ? newsContainer.value.scrollLeft + scrollAmount
        : newsContainer.value.scrollLeft - scrollAmount

      newsContainer.value.scrollTo({
        left: newScrollLeft,
        behavior: 'smooth'
      })

      // Scroll sonrası pozisyonu kontrol et
      setTimeout(() => {
        const { scrollLeft, scrollWidth, clientWidth } = newsContainer.value
        const maxScroll = scrollWidth - clientWidth
        
        // Sağ ve sol butonların görünürlüğünü güncelle
        canScrollLeft.value = scrollLeft > 0
        canScrollRight.value = scrollLeft < maxScroll - 1

        // Debug için kaldır
        // console.log({
        //   scrollLeft,
        //   maxScroll,
        //   canScrollLeft: canScrollLeft.value,
        //   canScrollRight: canScrollRight.value
        // })
      }, 300) // Scroll animasyonunun bitmesini bekle
    }

    const openNewsLink = (url) => {
      if (url) {
        ipcRenderer.send('open-external-link', url)
      }
    }

    const startDragging = (e) => {
      if (e.button !== 0) return // Sadece sol tık ile çalışsın
      isDraggingNews.value = true
      dragStartX.value = e.pageX
      dragStartScrollLeft.value = newsContainer.value.scrollLeft
      newsContainer.value.style.cursor = 'grabbing'
      newsContainer.value.style.userSelect = 'none'
    }

    const handleDragging = (e) => {
      if (!isDraggingNews.value) return
      e.preventDefault()
      
      const dx = e.pageX - dragStartX.value
      const scrollSpeed = 1.5 // Scroll hızı çarpanı
      const newScrollLeft = dragStartScrollLeft.value - (dx * scrollSpeed)
      
      // Scroll sınırlarını kontrol et
      const maxScroll = newsContainer.value.scrollWidth - newsContainer.value.clientWidth
      newsContainer.value.scrollLeft = Math.max(0, Math.min(newScrollLeft, maxScroll))
      
      // isAtEnd durumunu güncelle
      isAtEnd.value = newsContainer.value.scrollLeft <= 0
    }

    const stopDragging = () => {
      if (!isDraggingNews.value) return
      isDraggingNews.value = false
      newsContainer.value.style.cursor = 'grab'
      newsContainer.value.style.userSelect = ''
      
      // Momentum scroll efekti için son pozisyonu kaydet
      const finalScrollLeft = newsContainer.value.scrollLeft
      const momentum = Math.abs(dragStartScrollLeft.value - finalScrollLeft) > 50
      
      if (momentum) {
        const direction = dragStartScrollLeft.value > finalScrollLeft ? 1 : -1
        const targetScroll = finalScrollLeft + (direction * 100)
        
        newsContainer.value.scrollTo({
          left: targetScroll,
          behavior: 'smooth'
        })
      }
    }

    // News container'a hover style ekle
    onMounted(() => {
      if (newsContainer.value) {
        newsContainer.value.style.cursor = 'grab'
      }
    })

    // Scroll pozisyonunu kontrol et
    const checkScrollPosition = () => {
      if (!newsContainer.value) return
      
      const { scrollLeft, scrollWidth, clientWidth } = newsContainer.value
      const maxScroll = scrollWidth - clientWidth
      
      // Daha hassas kontrol
      canScrollLeft.value = Math.ceil(scrollLeft) > 0
      canScrollRight.value = Math.floor(scrollLeft) < maxScroll - 1

      // Debug için
      console.log({
        scrollLeft,
        maxScroll,
        canScrollLeft: canScrollLeft.value,
        canScrollRight: canScrollRight.value
      })
    }

    // Scroll event listener'ı daha sık kontrol etsin
    onMounted(() => {
      if (newsContainer.value) {
        const handleScroll = () => {
          requestAnimationFrame(checkScrollPosition)
        }
        
        newsContainer.value.addEventListener('scroll', handleScroll, { passive: true })
        
        // İlk yüklemede kontrol et
        nextTick(() => {
          checkScrollPosition()
        })
      }
    })

    onUnmounted(() => {
      if (newsContainer.value) {
        newsContainer.value.removeEventListener('scroll', checkScrollPosition)
      }
    })

    // Klasör seçme fonksiyonu
    const selectInstallPath = async () => {
      try {
      const result = await ipcRenderer.invoke('select-install-path')
      if (result) {
          // Hem store'a hem de reaktif ref'e yaz
        store.set('installPath', result)
          installPath.value = result
          
          // Bildirim göster
          showExternalNotification(t('settings'), t('installPathChanged'))
        }
      } catch (error) {
        console.error('Kurulum yolu seçme hatası:', error)
      }
    }

    // Bildirim ayarını değiştirme fonksiyonu
    const toggleNotifications = async () => {
      notificationsEnabled.value = !notificationsEnabled.value
      store.set('notificationsEnabled', notificationsEnabled.value)
      console.log('Bildirim ayarı:', notificationsEnabled.value)
      
      // Bildirim göster
      showExternalNotification(t('settings'), t('settingsSaved'))
    }

    // Haber bildirimlerini aç/kapat
    const toggleNewsNotifications = async () => {
      newsNotificationsEnabled.value = !newsNotificationsEnabled.value
      store.set('newsNotificationsEnabled', newsNotificationsEnabled.value)
      console.log('Haber bildirimi ayarı:', newsNotificationsEnabled.value)
      
      // Bildirim göster
      showExternalNotification(t('settings'), t('settingsSaved'))
    }
    
    const toggleUpdateNotifications = async () => {
      updateNotificationsEnabled.value = await ipcRenderer.invoke(
        'toggle-update-notifications',
        !updateNotificationsEnabled.value
      )
      
      // Bildirim göster
      showExternalNotification(t('settings'), t('settingsSaved'))
    }

    // Haber bildirimi gösterme
    onMounted(() => {
      ipcRenderer.on('show-news-notifications', (event, { news, lang }) => {
        if (newsNotificationsEnabled.value) {
          news.forEach(item => {
            const notification = new Notification(t('newsNotificationTitle'), {
              body: item.title,
              icon: item.image,
              tag: `news-${item.id}`,
              requireInteraction: true
            })

            notification.onclick = () => {
              if (item.url) {
                ipcRenderer.invoke('open-external-link', item.url)
              }
            }
          })
        }
      })
    })

    // Component unmount olduğunda listener'ı temizle
    onUnmounted(() => {
      ipcRenderer.removeAllListeners('show-news-notifications')
      ipcRenderer.removeAllListeners('manifest-updated')
      ipcRenderer.removeAllListeners('social-media-updated')
      ipcRenderer.removeAllListeners('background-updated')
      ipcRenderer.removeAllListeners('notice-updated')
      ipcRenderer.removeAllListeners('menu-updated')
    })

    // Link açma fonksiyonu
    const openExternalLink = async (url) => {
      if (url) {
        // Sistem varsayılan tarayıcısında aç
        await ipcRenderer.invoke('open-external-link', url);
      }
    }

    // Menü item tıklama
    const handleMenuItemClick = (url) => {
      openExternalLink(url)
    }

    // Sosyal medya listesini güncelle
    const updateSocialMedia = (manifest) => {
      if (manifest?.socialMedia) {
        socialMediaItems.value = manifest.socialMedia
      }
    }



    // Developer Tools kısayolu
    onMounted(() => {
      window.addEventListener('keydown', (e) => {
        // Ctrl + Shift + I veya F12
        if ((e.ctrlKey && e.shiftKey && e.key === 'I') || e.key === 'F12') {
          ipcRenderer.send('toggle-devtools')
        }
      })
    })

    // Başlangıçta ayarı al
    onMounted(async () => {
      updateNotificationsEnabled.value = await ipcRenderer.invoke('get-update-notifications-enabled')
    })

    let iconPath = path.join(__dirname, '..', '..', 'build', 'icon.png')

    // Güncelleme bildirimi dinleyicisi
    onMounted(() => {
      ipcRenderer.on('show-update-notification', (event, { files, lang }) => {
        if (updateNotificationsEnabled.value) {
          const notification = new Notification(t('notificationTitle'), {
            body: `${files.length} dosya için güncelleme mevcut`,
            width: 44,
            height: 44,
            icon: iconPath,
            tag: 'update-notification',
            requireInteraction: true
          })

          notification.onclick = () => {
            // Ayarlar sayfasına git
            currentPage.value = 'settings'
            mainWindow.show()
          }
        }
      })
    })

    // Component unmount olduğunda listener'ı temizle
    onUnmounted(() => {
      ipcRenderer.removeAllListeners('show-update-notification')
    })

    // URL'leri algılayıp link haline getiren fonksiyon
    const formatMessage = (message) => {
      if (!message) return ''
      
      // URL regex pattern
      const urlPattern = /(https?:\/\/[^\s]+)/g
      
      // Mesajdaki URL'leri link haline getir
      return message.replace(urlPattern, (url) => {
        return `<a href="#" class="text-blue-400 hover:text-blue-300 underline" onclick="window.postMessage({type: 'openLink', url: '${url}'}, '*')">${url}</a>`
      }).replace(/\n/g, '<br>')
    }

    // Link tıklamalarını dinle
    onMounted(() => {
      window.addEventListener('message', (event) => {
        if (event.data.type === 'openLink') {
          ipcRenderer.invoke('open-external-link', event.data.url)
        }
      })
    })

    onUnmounted(() => {
      window.removeEventListener('message', () => {})
    })

    // Sosyal medya hover renklerini belirle
    const getSocialMediaColor = (icon) => {
      /*if (icon.includes('fa-youtube')) return 'hover:text-[#FF0000]'
      if (icon.includes('fa-twitter')) return 'hover:text-[#1DA1F2]'
      if (icon.includes('fa-discord')) return 'hover:text-[#7289DA]'
      if (icon.includes('fa-instagram')) return 'hover:text-[#E4405F]'
      if (icon.includes('fa-facebook')) return 'hover:text-[#1877F2]'
      if (icon.includes('fa-twitch')) return 'hover:text-[#6441A4]'
      if (icon.includes('fa-telegram')) return 'hover:text-[#0088CC]'
      if (icon.includes('fa-reddit')) return 'hover:text-[#FF4500]'
      if (icon.includes('fa-whatsapp')) return 'hover:text-[#25D366]'
      if (icon.includes('fa-linkedin')) return 'hover:text-[#0A66C2]'
      if (icon.includes('fa-tiktok')) return 'hover:text-[#000000]'*/
      return 'hover:text-white' // Varsayılan renk
    }


    // Mp3 Player için state'ler
    const audioPlayer = ref(null)
    const currentTime = ref(0)
    const duration = ref(0)
    const audioProgress = ref(0)
    const showVolumeControl = ref(false)
    const volume = ref(store.get('mp3_volume', 100))
    const previousVolume = ref(100)
    let audioInitialized = false

    // Ses kontrolü fonksiyonları
    const toggleMute = () => {
      if (volume.value > 0) {
        previousVolume.value = volume.value
        volume.value = 0
      } else {
        volume.value = previousVolume.value
      }
      updateVolume()
    }

    const updateVolume = () => {
      if (audioPlayer.value) {
        audioPlayer.value.volume = volume.value / 100
        store.set('mp3_volume', volume.value)
      }
    }

    // Zaman formatı
    const formatTime = (seconds) => {
      if (!seconds) return '0:00'
      const mins = Math.floor(seconds / 60)
      const secs = Math.floor(seconds % 60)
      return `${mins}:${secs.toString().padStart(2, '0')}`
    }

    // Audio event handlers
    const onTimeUpdate = () => {
      if (!audioPlayer.value) return
      currentTime.value = audioPlayer.value.currentTime
      audioProgress.value = (currentTime.value / duration.value) * 100
    }

    const onLoadedMetadata = () => {
      if (!audioPlayer.value) return
      duration.value = audioPlayer.value.duration
    }

    const onCanPlayThrough = () => {
      if (!audioPlayer.value || audioInitialized) return
      
      // Only initialize and play if mp3_player_control.enabled is 1
      if (manifest.value?.mp3_player_control?.enabled === 1) {
      audioPlayer.value.volume = volume.value / 100
      audioPlayer.value.play()
      audioInitialized = true
      }
    }

    // Mp3 Player bölümünde
    watch(() => manifest.value?.mp3_player_control?.enabled, (newValue, oldValue) => {
      if (audioPlayer.value) {
        if (newValue === 1) {
          // enabled 1 olduğunda müziği başlat
          audioPlayer.value.volume = volume.value / 100
          audioPlayer.value.load()
          audioPlayer.value.play()
          audioInitialized = true
        } else {
          // enabled 0 olduğunda müziği durdur
          audioPlayer.value.pause()
          audioPlayer.value.currentTime = 0
          audioInitialized = false
        }
      }
    }, { immediate: true }) // immediate: true ile component mount olduğunda hemen çalışır

    // Mp3 Player güncellemelerini dinle
    onMounted(() => {
      ipcRenderer.on('mp3-player-updated', (event, mp3Data) => {
        if (manifest.value) {
          manifest.value.mp3_player_control = mp3Data
          
          // enabled durumuna göre müziği kontrol et
          if (audioPlayer.value) {
            if (mp3Data.enabled === 1) {
              audioPlayer.value.load()
              audioPlayer.value.play()
              audioInitialized = true
            } else {
              audioPlayer.value.pause()
              audioPlayer.value.currentTime = 0
              audioInitialized = false
            }
          }
        }
      })
    })

    // Component unmount olduğunda müziği durdur
    onUnmounted(() => {
      if (audioPlayer.value) {
        audioPlayer.value.pause()
        audioPlayer.value.currentTime = 0
      }
    })

    // Müziği durumu koruma fonksiyonu
    const restoreAudioState = () => {
      if (manifest.value?.mp3_player_control?.enabled === 1 && audioPlayer.value) {
        // Eğer müzik kontrolleri aktifse ve müzik çalar varsa
        console.log('Müzik durumu kontrol ediliyor')
        
        // Eğer müzik durdurulmuşsa ve mp3_player_control aktifse başlat
        if (audioPlayer.value.paused) {
          console.log('Müzik yeniden başlatılıyor')
          audioPlayer.value.play().catch(err => {
            console.error('Müzik oynatma hatası:', err)
          })
        }
      }
    }

    // Sayfa değişikliklerinde müzik çalmaya devam etmesi için
    watch(currentPage, (newPage, oldPage) => {
      // Her sayfa değişikliğinde müziği kontrol et - sadece apps değil tüm sayfalar için
      // Küçük bir gecikme ekleyerek DOM güncellemesinin tamamlanmasını bekle
      setTimeout(() => {
        // Eğer mp3_player_control etkinse ve müzik çalar varsa devam ettir
        if (manifest.value?.mp3_player_control?.enabled === 1 && audioPlayer.value) {
          console.log('Sayfa değişikliği tespit edildi:', oldPage, '->', newPage)
          console.log('Müzik durumu:', audioPlayer.value.paused ? 'Duraklatılmış' : 'Çalıyor')
          
          if (audioPlayer.value.paused) {
            // Müzik durmuşsa yeniden başlat
            console.log('Sayfa değişimi sonrası müzik yeniden başlatılıyor')
            audioPlayer.value.play().catch(err => {
              console.error('Müzik oynatma hatası:', err)
            })
          }
        }
      }, 50)
    })

    // Dışarı tıklandığında ses menüsünü kapat
    onMounted(() => {
      document.addEventListener('click', (e) => {
        if (showVolumeControl.value) {
          showVolumeControl.value = false
        }
      })
    })

    onUnmounted(() => {
      document.removeEventListener('click', closeVolumeControl)
    })

    // Ses menüsü kontrolü
    const toggleVolumeMenu = () => {
      showVolumeControl.value = !showVolumeControl.value
    }

    // Add these functions inside the setup function:
    const isAppInstalled = (app) => {
      try {
        if (!installPath.value || !app || !app.path) return false
        const filePath = path.join(installPath.value, app.path)
        return fs.existsSync(filePath)
      } catch (error) {
        console.error('isAppInstalled kontrolü sırasında hata:', error)
        return false
      }
    }

    const needsUpdate = async (app) => {
      if (!installPath.value) return false
      const filePath = path.join(installPath.value, app.path)
      if (!fs.existsSync(filePath)) return false
      
      const fileExists = await ipcRenderer.invoke('check-file', {
        path: filePath,
        size: app.size,
        hash: app.hash
      })
      return !fileExists
    }

    // Sayfa yüklendikten hemen sonra müziği başlat
    onMounted(() => {
      console.log('Component yüklendi, mp3 kontrolü yapılıyor')
      
      // Bir süre bekle ve ardından müzik kontrolünü gerçekleştir (DOM yüklenmesi için)
      setTimeout(() => {
        if (manifest.value?.mp3_player_control?.enabled === 1 && audioPlayer.value) {
          console.log('Müzik başlatılıyor')
          
          // Volume'u ayarla
          audioPlayer.value.volume = volume.value / 100
          
          // Müziği yükle ve oynat
          audioPlayer.value.load()
          audioPlayer.value.play().catch(err => {
            console.error('Müzik başlatma hatası:', err)
          })
        }
      }, 500)
    })
    
    // Mp3 Player güncellemelerini dinle

    // Apps sayfasını gösterme isteği için dinle
    onMounted(() => {
      ipcRenderer.on('show-apps-page', () => {
        currentPage.value = 'apps'
      })
      
      // App Files için eğer güncelleme yoksa kırmızı noktayı kontrol için
      ipcRenderer.on('app-files-updated', async (event, updatedFiles) => {
        console.log('App dosya güncellemeleri alındı:', updatedFiles.length)
        // Hemen app güncellemelerini kontrol et
        await checkAppsForUpdates()
      })
    })

    // IPC olaylarını dinle
    onMounted(() => {
      // Ayarları dinle
      ipcRenderer.on('update-settings', (event, settings) => {
        updateNotificationsEnabled.value = settings.updateNotificationsEnabled
      })
      
      // Manifesti dinle
      ipcRenderer.on('manifest-updated', (event, newManifest) => {
        updateNotificationsEnabled.value = newManifest.settings?.updateNotificationsEnabled
      })
    })

    const showGalleryApp = () => {
      currentPage.value = 'gallery'
    }

    // Bildirim yönetimi
    const notification = ref({
      show: false,
      visible: false,
      type: 'success', // 'success' veya 'info'
      message: ''
    })

    // Özel bildirim gösterme fonksiyonu
    const showNotification = (message, type = 'success') => {
      // Önceki bildirimi kapat
      notification.value.show = false
      notification.value.visible = false
      
      // Yeni bildirimi ayarla
      setTimeout(() => {
        notification.value.message = message
        notification.value.type = type
        notification.value.show = true
        
        // Gösterimi geciktir (animasyon için)
        setTimeout(() => {
          notification.value.visible = true
          
          // Otomatik kapatma
          setTimeout(() => {
            notification.value.visible = false
            setTimeout(() => notification.value.show = false, 300) // CSS transition süresi bittikten sonra kaldır
          }, 3000) // 3 saniye göster
        }, 10)
      }, 300)
    }

    // Bağımsız dış bildirim penceresi gösterme
    const showExternalNotification = (title, message) => {
      ipcRenderer.invoke('show-external-notification', { 
        title, 
        message, 
        duration: 3000,
        showAppsPage: false // App sayfasına yönlendirme kapalı
      }).catch(err => {
        console.error('Harici bildirim hatası:', err)
      })
    }

    // Her app için buton metnini asenkron sorgusuz hesaplayan fonksiyon
    const getButtonTextForApp = (app) => {
      try {
        if (!app || !app.id) return t('download')
        
        // Kurulum yolu yoksa indirme butonu göster
        if (!installPath.value) return t('download')
        
        // App'in dosyalarından exe dosyalarını bul
        const exeFile = app.files.find(f => f.path.toLowerCase().endsWith('.exe'))
        if (!exeFile) return t('download')
        
        // Exe dosyasının var olup olmadığını kontrol et
        const exePath = path.join(installPath.value, exeFile.path)
        
        // Exe dosyası yoksa indir
        if (!fs.existsSync(exePath)) {
          return t('download')
        }
        
        // Manifest üzerinden needsUpdate değerini doğrudan kullan
        // Detaylı kontrol checkAppsForUpdates fonksiyonunda yapılıyor
        if (app.needsUpdate === true) {
          console.log(`App ${app.id} - ${app.name} için güncelleme butonu gösteriliyor`)
          return t('update')
        }
        
        // Varsayılan olarak başlat butonu göster
        return t('start')
      } catch (error) {
        console.error(`App ID ${app.id} için buton metni hesaplama hatası:`, error)
        return t('download') // Hata durumunda indirme butonu göster
      }
    }
    
    // Dosya hash kontrolü yapan yardımcı fonksiyon
    const checkFileValid = async (filePath, hash, size) => {
      try {
        if (!fs.existsSync(filePath)) return false
        
        const stats = fs.statSync(filePath)
        if (stats.size !== size) return false
        
        // IPC üzerinden hash kontrolü yap
        const isValid = await ipcRenderer.invoke('check-file', {
          path: filePath,
          size: size,
          hash: hash
        })
        
        return isValid
      } catch (error) {
        console.error(`Dosya geçerlilik kontrolü hatası: ${filePath}`, error)
        return false
      }
    }

    // Manifest değişikliklerini dinle
    onMounted(() => {
      console.log('Manifest olayları dinleniyor...')
      
      // Manifest güncellemelerini dinle
      ipcRenderer.on('manifest-updated', async (event, newManifest) => {
        console.log('Manifest güncellemesi alındı')
        
        // Lokaldeki manifesti güncelle
        const oldManifest = manifest.value
        manifest.value = newManifest
        
        // Eski ve yeni app ID'lerini karşılaştır
        const oldAppIds = oldManifest?.apps?.map(app => app.id) || []
        const newAppIds = newManifest?.apps?.map(app => app.id) || []
        
        // Yeni eklenen app'leri bul
        const addedAppIds = newAppIds.filter(id => !oldAppIds.includes(id))
        
        // App güncellemelerini kontrol et
        const updatedCount = await checkAppsForUpdates()
        
        // Yeni eklenen uygulamalar varsa bildirim göster
        if (addedAppIds.length > 0) {
          const newApps = newManifest.apps.filter(app => addedAppIds.includes(app.id))
          const newAppNames = newApps.map(app => app.name).join(', ')
          showNotification(`Yeni uygulama(lar) eklendi: ${newAppNames}`, 'info')
        }
        
        
        
        // Apps sayfasında ise, sayfayı güncelle için forceUpdate tetikle
        if (currentPage.value === 'apps') {
          console.log('Apps sayfası güncelleniyor')
          
          // nextTick ile UI güncellemesini garantile
          nextTick(() => {
            // Buton metinlerini güncellemek için tüm appları yeniden işle
            for (const app of manifest.value.apps) {
              getButtonTextForApp(app)
            }
          })
        }
      })
      
      // App dosya güncellemeleri için dinlemeyi ekle
      ipcRenderer.on('app-files-updated', async (event, updatedFiles) => {
        console.log('App dosya güncellemeleri alındı:', updatedFiles.length)
        
        // Güncellemeleri kontrol et ve uygula
        const updatedCount = await checkAppsForUpdates()        
        
        // Apps sayfasında ise, sayfayı güncelle
        if (currentPage.value === 'apps') {
                    
          // nextTick ile UI güncellemesini garantile
          nextTick(() => {
            // Buton metinlerini güncellemek için tüm appları yeniden işle
            for (const app of manifest.value.apps) {
              getButtonTextForApp(app)
            }
          })
        }
      })
    })
    
    // Bildirimleri özelleştir
    onMounted(() => {
      // Apps sayfasını açma işlemini dinle, artık parametreli yapıyoruz
      ipcRenderer.on('show-apps-page', (event, options = {}) => {
        // Varsayılan olarak sayfa değiştirmeyi devre dışı bırakalım
        const showPage = options?.showPage ?? false;
        
        if (showPage) {
          currentPage.value = 'apps'
          
          // Apps sayfasına geçtikten sonra güncellemeleri kontrol et
          nextTick(async () => {
            await checkAppsForUpdates()
          })
        } else {
          // Sadece güncelleme yap, sayfa değiştirme
          checkAppsForUpdates()
        }
      })
    })
    
    // Sayfa değiştiğinde otomatik güncelleme kontrolü yap
    watch(currentPage, async (newPage) => {
      if (newPage === 'apps') {
        console.log('Apps sayfasına geçildi, güncellemeler kontrol ediliyor...')
        await checkAppsForUpdates()
      }
    })

    return {
      isDragging,
      dragOffset,
      minimizeWindow,
      closeWindow,
      formatSize,
      calculateAppSize,
      isLoading,
      isButtonsLoading,
      connectionError,
      importantNotice,
      isDownloading,
      downloadProgress,
      news,
      background,
      menuItems,
      formatDate,
      handleAppAction,
      openSettings,
      startDrag,
      onDrag,
      stopDrag,
      currentFile,
      downloadedSize,
      totalSize,
      newsContainer,
      scrollNews,
      isAtEnd,
      startAutoScroll,
      stopAutoScroll,
      openNewsLink,
      socialMediaItems,
      currentLang,
      availableLanguages,
      showLangMenu,
      changeLang,
      t,
      currentNotice,
      startDragging,
      handleDragging,
      stopDragging,
      canScrollLeft,
      canScrollRight,
      checkScrollPosition,
      currentPage,
      installPath,
      selectInstallPath,
      notificationsEnabled,
      toggleNotifications,
      newsNotificationsEnabled,
      toggleNewsNotifications,
      handleMenuItemClick,
      manifest,
      videoPlayer,
      allFilesExist,
      updateNotificationsEnabled,
      toggleUpdateNotifications,
      formatMessage,
      getSocialMediaColor,
      isPageReady,
      getLogoFontStyle,
      versionFilter,
      statusFilter,
      isVersionDropdownOpen,
      isStatusDropdownOpen,
      audioPlayer,
      currentTime,
      duration,
      audioProgress,
      showVolumeControl,
      toggleVolumeMenu,
      toggleMute,
      volume,
      updateVolume,
      formatTime,
      onTimeUpdate,
      onLoadedMetadata,
      onCanPlayThrough,
      restoreAudioState,
      isVirtualMachine,
      isAppInstalled,
      needsUpdate,
      buttonText,
      checkAppStatus,
      hasAppUpdates,
      checkAppsForUpdates,
      showGalleryApp,
      notification,
      showNotification,
      showExternalNotification,
      getButtonTextForApp,
    }
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import url('https://launcher.khepridev.xyz/fontawesome/css/all.min.css');

body {
  @apply bg-transparent font-['Inter'];
  -webkit-font-smoothing: antialiased;
}

::-webkit-scrollbar {
  display: none;
}

.scrollbar-custom::-webkit-scrollbar {
  display: block;
  height: 8px;
  width: 8px;
}

.scrollbar-custom::-webkit-scrollbar-track {
  @apply bg-white/5 rounded-full;
}

.scrollbar-custom::-webkit-scrollbar-thumb {
  @apply bg-white/20 rounded-full hover:bg-white/30 transition-colors;
}

.draggable {
  -webkit-app-region: drag;
  cursor: move;
}

.no-drag {
  -webkit-app-region: no-drag;
}

/* Video için ek stiller */
video {
  object-fit: cover;
  pointer-events: none;
}

/* News container için smooth scroll */
.scrollbar-custom {
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
}

/* Sürükleme sırasında text seçimini engelle */
.scrollbar-custom * {
  user-select: none;
}

/* Sayfa geçiş animasyonları */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.page-margin {
  margin-top: -20px;
}

/* Settings scrollbar için özel stil */
.settings-scrollbar::-webkit-scrollbar {
  width: 8px;
  display: block;
}

.settings-scrollbar::-webkit-scrollbar-track {
  @apply bg-white/5 rounded-full;
}

.settings-scrollbar::-webkit-scrollbar-thumb {
  @apply bg-white/20 rounded-full hover:bg-white/30 transition-colors;
}

/* Link hover efekti için transition ekleyelim */
a {
  transition: all 0.2s ease;
  text-decoration: none;
}

.text-xs a {
  font-size: inherit;
  line-height: inherit;
}

/* Windows 10 için ek performans optimizasyonları */
.fixed {
  will-change: transform;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
}

video {
  will-change: transform;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
}

/* Arka plan geçişleri için ek stil */
.bg-cover {
  will-change: transform, opacity;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  transition: opacity 0.3s ease;
}

/* Kırmızı nokta için yeni animasyon */
@keyframes pulsate {
  0% {
    transform: scale(0.8);
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  }
  
  50% {
    transform: scale(1.1);
    box-shadow: 0 0 0 5px rgba(239, 68, 68, 0);
  }
  
  100% {
    transform: scale(0.8);
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
  }
}

.pulsating-dot {
  animation: pulsate 1.5s ease-in-out infinite;
}
</style>