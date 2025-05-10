<template>
  <!-- Loading Screen -->
  <div v-if="isLoading || !isPageReady || (manifest?.maintenance?.enabled === 1)" 
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
      <template v-else>
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
    <!-- Sürüklenebilir header -->
    <div class="absolute top-0 left-0 w-full h-8 bg-transparent z-40 draggable"></div>

    <!-- Arka plan -->
    <div class="absolute inset-0">
      <!-- Arka Plan Skeleton -->
      <div v-if="isButtonsLoading" class="absolute inset-0">
        <!-- Gradient background skeleton -->
        <div class="absolute inset-0 bg-gradient-to-b from-gray-900 to-black animate-pulse">
          <!-- Overlay pattern -->
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.05)_0%,transparent_100%)]"></div>
        </div>
        <!-- Karartma overlay -->
        <div class="absolute inset-0 bg-black/60"></div>
      </div>

      <!-- Gerçek Arka Plan -->
      <template v-else>
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
      </template>
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
              
              <!-- New Server Button Skeleton -->
              <div class="w-24 h-7 bg-white/5 rounded animate-pulse"></div>
              
              <!-- Servers Button Skeleton -->
              <div class="w-20 h-7 bg-white/5 rounded animate-pulse"></div>
            </div>
          </template>

          <!-- Gerçek Menu Items -->
          <template v-else-if="currentPage === 'home' || currentPage === 'new_server' || currentPage === 'servers'">
            <a @click="currentPage = 'home'"
               href="#"
               class="hover:text-white text-sm transition-colors hover:scale-85 transform duration-200 px-2 py-1 rounded-md hover:bg-black/50 select-none cursor-pointer"
               :class="{ 'bg-black/50 text-white': currentPage === 'home', 'text-white/70': currentPage !== 'home' }">
               {{ t('home') }}
            </a>
            <a v-for="item in menuItems" 
               :key="item.id"
               @click.prevent="handleMenuItemClick(item.url)"
               href="#"
               class="hover:text-white text-sm transition-colors hover:scale-105 transform duration-200 px-2 py-1 rounded-md hover:bg-black/50 select-none cursor-pointer text-white/70">
              {{ item.title }}
            </a>

            <!-- New Server Button -->
            <a @click="currentPage = 'new_server'"
               href="#"
               class="hover:text-white text-sm transition-colors hover:scale-85 transform duration-200 px-2 py-1 rounded-md hover:bg-black/50 select-none cursor-pointer"
               :class="{ 'bg-black/50 text-white': currentPage === 'new_server', 'text-white/70': currentPage !== 'new_server' }">
               {{ t('new_server') }}
            </a>
            <a @click="currentPage = 'servers'"
               href="#"
               class="hover:text-white text-sm transition-colors hover:scale-85 transform duration-200 px-2 py-1 rounded-md hover:bg-black/50 select-none cursor-pointer"
               :class="{ 'bg-black/50 text-white': currentPage === 'servers', 'text-white/70': currentPage !== 'servers' }">
               {{ t('servers') }}
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
                @click="currentPage = 'home'"
                class="text-white/70 hover:text-white text-sm transition-colors hover:scale-105 transform duration-200 px-2 h-6 rounded-md hover:bg-white/10 select-none flex items-center gap-2"
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
                 'bg-green-500/20 border-green-500/20': manifest.importantNotice.type === 'success',
                 'bg-red-500/20 border-red-500/20 shadow-md shadow-red-500/20': manifest.importantNotice.type === 'danger',
                 'bg-yellow-500/20 border-yellow-500/20 shadow-md shadow-yellow-500/20': manifest.importantNotice.type === 'warning',
                 'bg-blue-500/20 border-blue-500/20 shadow-md shadow-blue-500/20': manifest.importantNotice.type === 'info',
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

          <audio 
            v-if="manifest?.mp3_player_control?.enabled === 1"
            ref="audioPlayer"
            :src="manifest.mp3_player_control.player[0].url"
            @timeupdate="onTimeUpdate"
            @loadedmetadata="onLoadedMetadata"
            @canplaythrough="onCanPlayThrough"
            preload="auto">
          </audio>

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

        <!-- NewServer Sayfası -->
        <div v-else-if="currentPage === 'new_server'" class="relative flex-1 page-margin">
          <div class="glass-effect rounded-2xl mt-2">          
            <!-- New Server List Container -->
            <div class="max-h-[380px] overflow-y-auto scrollbar-custom p-4">
              <div class="space-y-3">
                <div v-for="server in new_server" 
                     :key="server.id"
                     class="flex items-center gap-3 h-[52px] px-4 rounded-[6px] bg-[#111111]/60 backdrop-blur-sm border border-[#232323]/50 
                            transition-all duration-300 hover:bg-black/40 server-pulse">
                  <!-- Server Icon -->
                  <img :src="server.image" 
                       :alt="server.title"
                       class="w-7 h-7 rounded-[4px] object-cover" />
                  
                  <!-- Server Info -->
                  <div class="flex-1 flex items-center justify-between">
                    <div class="flex flex-col">
                      <span class="text-white/90 text-sm">{{ server.title }}</span>
                    </div>
                    
                    <div class="flex items-center gap-4">
                      <!-- Status -->
                      <div class="flex items-center gap-2 px-2 py-1 rounded"
                           :class="{
                             'bg-[#00ff9d]/10': server.status === 1,
                             'bg-[#ff4747]/10': server.status === 2,
                             'bg-[#ffd600]/10': server.status === 3
                           }">
                        <span class="text-sm"
                              :class="{
                                'text-[#00ff9d]': server.status === 1,
                                'text-[#ff4747]': server.status === 2,
                                'text-[#ffd600]': server.status === 3
                              }">
                          {{ getServerStatus(String(server.status)) }}
                        </span>
                        <div class="w-1.5 h-1.5 rounded-full"
                             :class="{
                               'bg-[#00ff9d]': server.status === 1,
                               'bg-[#ff4747]': server.status === 2,
                               'bg-[#ffd600]': server.status === 3
                             }">
                        </div>
                      </div>
                    </div>

                    <!-- Version Badge -->
                    <span class="text-[10px] px-2 py-0.5 rounded ml-4"
                          :class="{
                            'bg-gradient-to-r from-[#ffffff05] to-[#666666] text-white': server.versiyon === 'Base',
                            'bg-gradient-to-r from-[#3498db05] to-[#2980b9] text-white': server.versiyon === 'Prime',
                            'bg-gradient-to-r from-[#e74c3c05] to-[#c0392b] text-white': server.versiyon === 'Elite'
                          }">
                      {{ server.versiyon }}
                    </span>

                    <!-- Go Button -->
                    <button @click="handleMenuItemClick(server.url)"
                            class="px-3 py-1 text-xs text-white/70 bg-[#232323] rounded-[4px] hover:bg-[#2a2a2a] transition-all ml-4">
                      Go →
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- NewServer Sayfası için Skeleton -->
        <div v-if="currentPage === 'new_server' && !new_server.length" class="relative flex-1 page-margin">
          <div class="glass-effect rounded-2xl mt-2">
            <div class="p-4">
              <div class="space-y-3">
                <div v-for="i in 5" :key="i" 
                     class="flex items-center gap-3 h-[52px] px-4 rounded-[6px] bg-gray-700/20 animate-pulse">
                  <!-- Icon Skeleton -->
                  <div class="w-7 h-7 rounded-[4px] bg-gray-600/20"></div>
                  <!-- Content Skeleton -->
                  <div class="flex-1 flex items-center justify-between">
                    <div class="h-4 w-32 bg-gray-600/20 rounded"></div>
                    <div class="flex items-center gap-4">
                      <div class="h-4 w-20 bg-gray-600/20 rounded"></div>
                      <div class="h-4 w-16 bg-gray-600/20 rounded"></div>
                      <div class="h-6 w-12 bg-gray-600/20 rounded"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Servers Sayfası -->
        <div v-else-if="currentPage === 'servers'" class="relative flex-1 page-margin">
          <div class="glass-effect rounded-2xl mt-2">          
            <!-- Header ve Arama -->
            <div class="flex items-center justify-between p-4">
              <h2 class="text-white/90 text-sm font-medium select-none">{{ t('servers') }}</h2>

              <div class="flex items-center gap-3">
                <!-- Version Filter Dropdown -->
                <div class="relative" 
                     @click="() => {
                       isVersionDropdownOpen = !isVersionDropdownOpen
                       isStatusDropdownOpen = false
                     }" 
                     v-click-outside="() => isVersionDropdownOpen = false">
                  <button class="flex items-center gap-2 px-3 py-1.5 text-sm text-white/70 bg-black/30 border border-white/10 rounded-lg hover:bg-black/40 transition-all">
                    <span>{{ versionFilter === 'all' ? t('allVersions') : versionFilter }}</span>
                    <i class="fa-light fa-chevron-down text-xs transition-transform duration-200"
                       :class="{ 'rotate-180': isVersionDropdownOpen }"></i>
                  </button>
                  <!-- Version Dropdown Menu -->
                  <div v-show="isVersionDropdownOpen" 
                       class="absolute top-full right-0 mt-1 w-32 py-1 bg-black/80 border border-white/10 rounded-lg shadow-xl z-50">
                    <button v-for="version in ['all', 'Base', 'Prime', 'Elite']" 
                            :key="version"
                            @click="versionFilter = version"
                            class="w-full px-3 py-1.5 text-left text-sm hover:bg-white/5 transition-all"
                            :class="versionFilter === version ? 'text-white' : 'text-white/70'">
                      {{ version === 'all' ? t('allVersions') : version }}
                    </button>
                  </div>
                </div>

                <!-- Status Filter Dropdown -->
                <div class="relative" 
                     @click="() => {
                       isStatusDropdownOpen = !isStatusDropdownOpen
                       isVersionDropdownOpen = false
                     }" 
                     v-click-outside="() => isStatusDropdownOpen = false">
                  <button class="flex items-center gap-2 px-3 py-1.5 text-sm text-white/70 bg-black/30 border border-white/10 rounded-lg hover:bg-black/40 transition-all">
                    <span>{{ statusFilter === 'all' ? t('allStatus') : getServerStatus(statusFilter) }}</span>
                    <i class="fa-light fa-chevron-down text-xs transition-transform duration-200"
                       :class="{ 'rotate-180': isStatusDropdownOpen }"></i>
                  </button>
                  <!-- Status Dropdown Menu -->
                  <div v-show="isStatusDropdownOpen" 
                       class="absolute top-full right-0 mt-1 w-32 py-1 bg-black/80 border border-white/10 rounded-lg shadow-xl z-50">
                    <button v-for="status in ['all', '1', '2', '3']" 
                            :key="status"
                            @click="statusFilter = status"
                            class="w-full px-3 py-1.5 text-left text-sm hover:bg-white/5 transition-all"
                            :class="statusFilter === status ? 'text-white' : 'text-white/70'">
                      {{ status === 'all' ? t('allStatus') : getServerStatus(status) }}
                    </button>
                  </div>
                </div>

                <!-- Search Input -->
                <div class="relative">
                  <input v-model="serverSearchQuery" 
                         type="text" 
                         :placeholder="t('search')"
                         class="bg-black/30 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-white/90 w-48
                                placeholder:text-white/30 focus:outline-none focus:border-white/20" />
                  <i class="fa-light fa-search absolute right-3 top-1/2 -translate-y-1/2 text-white/30 text-sm"></i>
                </div>
              </div>
            </div>

            <!-- Servers List Container -->
            <div class="h-[315px] overflow-y-scroll scrollbar-custom p-4">
              <div class="space-y-3">
                <div v-for="server in filteredServers" 
                     :key="server.server_name"
                     class="flex items-center gap-3 h-[52px] px-4 rounded-[6px] bg-[#111111]/60 backdrop-blur-sm border border-[#232323]/50 
                            transition-all duration-300 hover:bg-black/40 server-pulse">
                  <!-- Server Icon -->
                  <img :src="server.server_img" 
                       :alt="server.server_name"
                       class="w-7 h-7 rounded-[4px] object-cover" />
                  
                  <!-- Server Info -->
                  <div class="flex-1 flex items-center justify-between">
                    <div class="flex flex-col">
                      <span class="text-white/90 text-sm">{{ server.server_name }}</span>
                      
                    </div>
                    <div class="flex items-center gap-2 px-2 py-1 rounded"
                         :class="{
                           'bg-[#00ff9d]/10': server.server_status === '1',
                           'bg-[#ff4747]/10': server.server_status === '2',
                           'bg-[#ffd600]/10': server.server_status === '3'
                         }">
                      <span class="text-sm"
                            :class="{
                              'text-[#00ff9d]': server.server_status === '1',
                              'text-[#ff4747]': server.server_status === '2',
                              'text-[#ffd600]': server.server_status === '3'
                            }">
                        {{ getServerStatus(server.server_status) }}
                      </span>
                      <div class="w-1.5 h-1.5 rounded-full"
                           :class="{
                             'bg-[#00ff9d]': server.server_status === '1',
                             'bg-[#ff4747]': server.server_status === '2',
                             'bg-[#ffd600]': server.server_status === '3'
                           }">
                      </div>
                    </div>
                    <!-- Version Badge -->
                    <span class="text-[10px] px-2 py-0.5 rounded"
                            :class="{
                              'bg-gradient-to-r from-[#ffffff05] to-[#666666] text-white': server.server_version === 'Base',
                              'bg-gradient-to-r from-[#3498db05] to-[#2980b9] text-white': server.server_version === 'Prime',
                              'bg-gradient-to-r from-[#e74c3c05] to-[#c0392b] text-white': server.server_version === 'Elite'
                            }">
                        {{ server.server_version }}
                    </span>
                    <!-- Go Button -->
                    <button @click="handleMenuItemClick(server.server_url)"
                            class="px-3 py-1 text-xs text-white/70 bg-[#232323] rounded-[4px] hover:bg-[#2a2a2a] transition-all ml-4">
                      Go →
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Servers Sayfası için Skeleton -->
        <div v-if="currentPage === 'servers' && !servers.length" class="relative flex-1 page-margin">
          <div class="glass-effect rounded-2xl mt-2">
            <!-- Header Skeleton -->
            <div class="flex items-center justify-between p-4">
              <div class="h-5 w-24 bg-gray-700/20 rounded animate-pulse"></div>
              <div class="flex items-center gap-3">
                <div class="h-8 w-32 bg-gray-700/20 rounded animate-pulse"></div>
                <div class="h-8 w-32 bg-gray-700/20 rounded animate-pulse"></div>
                <div class="h-8 w-48 bg-gray-700/20 rounded animate-pulse"></div>
              </div>
            </div>
            <!-- Server List Skeleton -->
            <div class="p-4">
              <div class="space-y-3">
                <div v-for="i in 5" :key="i" 
                     class="flex items-center gap-3 h-[52px] px-4 rounded-[6px] bg-gray-700/20 animate-pulse">
                  <!-- Icon Skeleton -->
                  <div class="w-7 h-7 rounded-[4px] bg-gray-600/20"></div>
                  <!-- Content Skeleton -->
                  <div class="flex-1 flex items-center justify-between">
                    <div class="h-4 w-32 bg-gray-600/20 rounded"></div>
                    <div class="flex items-center gap-4">
                      <div class="h-4 w-20 bg-gray-600/20 rounded"></div>
                      <div class="h-4 w-16 bg-gray-600/20 rounded"></div>
                      <div class="h-6 w-12 bg-gray-600/20 rounded"></div>
                    </div>
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
              <label class="block text-white mb-2 select-none">{{ t('installPath') }}</label>
              <div class="flex gap-3">
                <input type="text" 
                       v-model="installPath" 
                       class="flex-1 bg-black/30 border border-white/10 rounded-lg px-2 py-1 text-white" 
                       readonly />
                <button @click="selectInstallPath" 
                        class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white transition-all select-none">
                  {{ t('select') }}
                </button>
              </div>
            </div>

            <!-- Bildirim Ayarları -->
            <div class="space-y-4">
              <!-- Genel Bildirimler -->
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-white font-medium mb-1 select-none">{{ t('notifications') }}</h3>
                  <p class="text-white/50 text-sm select-none">
                    {{ notificationsEnabled ? t('notificationsEnabled') : t('notificationsDisabled') }}
                  </p>
                </div>
                <button @click="toggleNotifications" 
                        class="w-12 h-6 rounded-full relative transition-colors select-none"
                        :class="notificationsEnabled ? 'bg-blue-500' : 'bg-white/10'">
                  <div class="w-5 h-5 rounded-full bg-white absolute top-0.5 transition-transform"
                       :class="notificationsEnabled ? 'translate-x-6' : 'translate-x-0.5'">
                  </div>
                </button>
              </div>

              <!-- Haber Bildirimleri -->
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-white font-medium mb-1 select-none">{{ t('newsNotifications') }}</h3>
                  <p class="text-white/50 text-sm select-none">
                    {{ newsNotificationsEnabled ? t('newsNotificationsEnabled') : t('newsNotificationsDisabled') }}
                  </p>
                </div>
                <button @click="toggleNewsNotifications" 
                        class="w-12 h-6 rounded-full relative transition-colors select-none"
                        :class="newsNotificationsEnabled ? 'bg-blue-500' : 'bg-white/10'">
                  <div class="w-5 h-5 rounded-full bg-white absolute top-0.5 transition-transform"
                       :class="newsNotificationsEnabled ? 'translate-x-6' : 'translate-x-0.5'">
                  </div>
                </button>
              </div>

              <!-- Güncelleme Bildirimleri -->
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-white/90">{{ t('updateNotifications') }}</div>
                  <div class="text-white/50 text-sm">
                    {{ updateNotificationsEnabled ? t('updateNotificationsEnabled') : t('updateNotificationsDisabled') }}
                  </div>
                </div>
                <button
                  @click="toggleUpdateNotifications"
                  class="w-12 h-6 rounded-full relative transition-colors duration-200"
                  :class="updateNotificationsEnabled ? 'bg-blue-500' : 'bg-white/10'"
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
      </transition>

      <!-- Buttons Skeleton -->
      <div v-if="isButtonsLoading" class="absolute bottom-4 left-4 right-4 flex items-center justify-between">
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
      <div v-else class="absolute bottom-4 left-4 right-4 flex items-center justify-between">
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
          v-if="currentPage === 'home'"
          @click="currentPage = 'settings'"
          class="px-3 py-2 bg-white/10 hover:bg-white/20 rounded transition-all duration-300 mr-3"
        >
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </button>

        <!-- Start Button -->
        <button 
          v-if="!needsRestart"
          @click="checkAndStartGame"
          :class="{
            'px-3 py-2 rounded transition-all duration-300 text-sm font-medium': true,
            'bg-green-500/30 hover:bg-green-500/40 text-white/90': !isDownloading && !allFilesExist,
            'bg-blue-500/30 hover:bg-blue-500/40 text-white/90': !isDownloading && allFilesExist,
            'bg-black/40 text-white/70 cursor-wait': isDownloading
          }"
        >
          <span v-if="isDownloading">{{ t('downloading') }}</span>
          <span v-else>{{ allFilesExist ? t('start') : t('download') }}</span>
        </button>

        <!-- Restart Button -->
        <button 
          v-else
          @click="restartApp"
          class="px-3 py-2 rounded transition-all duration-300 text-sm font-medium bg-pink-600/30 hover:bg-pink-600/40 text-white/90 flex items-center gap-2"
        >
          <svg 
            class="w-4 h-4 animate-bounce-rotate" 
            viewBox="0 0 24 24" 
            fill="none" 
            stroke="currentColor" 
            stroke-width="2" 
            stroke-linecap="round" 
            stroke-linejoin="round"
          >
            <path d="M21 12a9 9 0 11-3-6.74"></path>
            <path d="M12 8v4l3 3"></path>
          </svg>
          {{ t('restart') }}
        </button>
      </div>
    </div>
  </div>

</template>

<script>
import { ref, onMounted, onUnmounted, computed, nextTick, watch } from 'vue'
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
    const installPath = computed(() => store.get('installPath') || '')
    const notificationsEnabled = ref(store.get('notificationsEnabled', true))
    const newsNotificationsEnabled = ref(store.get('newsNotificationsEnabled', true))
    const new_server = ref([])
    const manifest = ref(null)
    const videoPlayer = ref(null)
    const updateNotificationsEnabled = ref(true)
    const isPageReady = ref(false)
    const serverSearchQuery = ref('')
    const servers = ref([])
    const versionFilter = ref('all')
    const statusFilter = ref('all')
    const isVersionDropdownOpen = ref(false)
    const isStatusDropdownOpen = ref(false)
    const needsRestart = ref(false)

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

    // Manifest kontrolünü güncelleyelim
    onMounted(async () => {
      try {
        isLoading.value = true
        isButtonsLoading.value = true
        
        // Manifest'i al
        const newManifest = await ipcRenderer.invoke('get-manifest')
        if (newManifest) {
          manifest.value = newManifest
          
          // Dil ayarını kontrol et
          if (!currentLang.value) {
            currentLang.value = store.get('language') || 'tr'
          }
          
          // Arka plan ve diğer verileri yükle
          await new Promise(resolve => {
            // Arka plan ayarlarını uygula
            if (newManifest.background) {
              background.value = newManifest.background
              if (background.value.mode === 2 && videoPlayer.value) {
                videoPlayer.value.addEventListener('loadeddata', resolve, { once: true })
                videoPlayer.value.load()
              } else {
                // Resim arka planı için kısa bir gecikme ekleyelim
                setTimeout(resolve, 100)
              }
            } else {
              resolve()
            }
          })
          
          // Diğer verileri ayarla
          if (newManifest.news) {
            news.value = newManifest.news
          }
          
          if (newManifest.socialMedia) {
            socialMediaItems.value = newManifest.socialMedia
          }

          // Sayfa hazır
          isPageReady.value = true
          
          // Loading spinner'ı kapat
          isLoading.value = false
          
          // Button kontrollerini yükle ve skeleton'u 1.5 saniye göster
          setTimeout(() => {
            isButtonsLoading.value = false
          }, 700) // 1000ms = 1 saniye
        }
      } catch (error) {
        console.error('Manifest yükleme hatası:', error)
        isLoading.value = false
      }
    })

    // Manifest ve güncelleme dinleyicilerini ekleyelim
    onMounted(() => {
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
      ipcRenderer.on('menu-updated', (event, { translations: newTranslations, currentLang }) => {
        translations.value = newTranslations
        if (currentLang) {
          currentLang.value = currentLang
        }
      })
    })

    // Component unmount olduğunda listener'ları temizleyelim
    onUnmounted(() => {
      ipcRenderer.removeAllListeners('news-updated')
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

    const allFilesExist = ref(false)

    // Dosya kontrolü için ayrı bir fonksiyon
    const checkFiles = async () => {
      if (!manifest.value?.files || !installPath.value) return false

      for (const file of manifest.value.files) {
        const filePath = path.join(installPath.value, file.path)
        const exists = await ipcRenderer.invoke('check-file', {
          path: filePath,
          size: file.size,
          hash: file.hash
        })
        if (!exists) return false
      }
      return true
    }

    // Dosya durumunu kontrol et ve güncelle
    watch([manifest, installPath], async () => {
      allFilesExist.value = await checkFiles()
    }, { immediate: true })

    const checkAndStartGame = async () => {
      try {
        if (!installPath.value) {
          alert(t('selectInstallPath'))
          return
        }

        // Dosyaları kontrol et
        const filesToDownload = []
        for (const file of manifest.value.files) {
          const filePath = path.join(installPath.value, file.path)
          const exists = await ipcRenderer.invoke('check-file', {
            path: filePath,
            size: file.size,
            hash: file.hash
          })
          if (!exists) {
            filesToDownload.push(file)
          }
        }

        // Güncelleme gerekiyorsa
        if (filesToDownload.length > 0) {
          isDownloading.value = true
          downloadProgress.value = 0
          
          for (const file of filesToDownload) {
            currentFile.value = path.basename(file.path)
            const filePath = path.join(installPath.value, file.path)
            
            try {
              await ipcRenderer.invoke('download-file', {
                url: file.url,
                path: filePath,
                hash: file.hash
              })
            } catch (error) {
              console.error('İndirme hatası:', error)
              throw error
            }
          }

          // İndirme tamamlandığında
          isDownloading.value = false
          currentFile.value = ''
          downloadProgress.value = 0
          needsRestart.value = true
          return
        }

        // Tüm dosyalar güncel ise oyunu başlat
        if (!manifest.value?.files?.[0]?.path) {
          alert(t('clientPathError') || 'Client path not found in manifest!') // Çeviri eklenebilir
          return
        }

        const clientPath = path.join(installPath.value, manifest.value.files[0].path)
        await ipcRenderer.invoke('start-client', clientPath)

      } catch (error) {
        console.error('Hata:', error)
        isDownloading.value = false
        currentFile.value = ''
        downloadProgress.value = 0
        alert(`Hata: ${error.message}`)
      }
    }

    // Restart fonksiyonu
    const restartApp = () => {
      ipcRenderer.send('restart-app')
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
      const result = await ipcRenderer.invoke('select-install-path')
      if (result) {
        // Yeni yolu seçtikten sonra app klasörünü oluştur
        const appDir = path.join(result, 'app')
        if (!fs.existsSync(appDir)) {
          fs.mkdirSync(appDir, { recursive: true })
        }
        // Yeni yolu kaydet
        store.set('installPath', result)
        
        // Kullanıcıya bilgi ver
        ipcRenderer.invoke('show-notification', {
          title: t('success'),
          body: t('installPathChanged')
        })
      }
    }

    // Bildirim ayarını değiştirme fonksiyonu
    const toggleNotifications = () => {
      notificationsEnabled.value = !notificationsEnabled.value
      store.set('notificationsEnabled', notificationsEnabled.value)
      console.log('Bildirim ayarı:', notificationsEnabled.value)
    }

    // Haber bildirimlerini aç/kapat
    const toggleNewsNotifications = () => {
      newsNotificationsEnabled.value = !newsNotificationsEnabled.value
      store.set('newsNotificationsEnabled', newsNotificationsEnabled.value)
      console.log('Haber bildirimi ayarı:', newsNotificationsEnabled.value)
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

    // Server status çevirisi için fonksiyon
    const getServerStatus = (status) => {
      switch (status) {
        case '1':
          return t('online')
        case '2':
          return t('offline')
        case '3':
          return t('maintenance')
        default:
          return status
      }
    }

    // New Server listesi için kontrol
    onMounted(async () => {
      const checkNewServers = async () => {
        try {
          manifest.value = await ipcRenderer.invoke('get-manifest')
          if (manifest.value?.new_server) {
            new_server.value = manifest.value.new_server
          }
        } catch (error) {
          console.error('New Server listesi güncellenirken hata:', error)
        }
      }
      
      // İlk yükleme
      await checkNewServers()
      
      // Periyodik kontrol
      const newServerInterval = setInterval(async () => {
        await checkNewServers()
      }, manifest.value?.settings?.checkIntervals?.new_server || 3000)
      
      // Component unmount olduğunda interval'i temizle
      onUnmounted(() => {
        clearInterval(newServerInterval)
      })
    })

    // Servers listesi için ayrı kontrol
    onMounted(async () => {
      const checkServers = async () => {
        try {
          const manifest = await ipcRenderer.invoke('get-manifest')
          if (manifest?.servers) {
            const response = await fetch(manifest.servers)
            const data = await response.json()
            servers.value = data
          }
        } catch (error) {
          console.error('Server listesi güncellenirken hata:', error)
        }
      }
      
      // İlk yükleme
      await checkServers()
      
      // Periyodik kontrol
      const serversInterval = setInterval(async () => {
        await checkServers()
      }, manifest.value?.settings?.checkIntervals?.servers || 3000)
      
      // Component unmount olduğunda interval'i temizle
      onUnmounted(() => {
        clearInterval(serversInterval)
      })
    })

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
    
    // Toggle fonksiyonu
    const toggleUpdateNotifications = async () => {
      updateNotificationsEnabled.value = await ipcRenderer.invoke(
        'toggle-update-notifications',
        !updateNotificationsEnabled.value
      )
    }

    // Güncelleme bildirimi dinleyicisi
    onMounted(() => {
      ipcRenderer.on('show-update-notification', (event, { files, lang }) => {
        if (updateNotificationsEnabled.value) {
          const notification = new Notification(t('notificationTitle'), {
            body: `${files.length} dosya için güncelleme mevcut`,
            icon: path.join(__dirname, 'icon.png'),
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

    // Server arama işlemi
    const filteredServers = computed(() => {
      let filtered = servers.value

      // Version filtresi
      if (versionFilter.value !== 'all') {
        filtered = filtered.filter(server => server.server_version === versionFilter.value)
      }

      // Status filtresi
      if (statusFilter.value !== 'all') {
        filtered = filtered.filter(server => server.server_status === statusFilter.value)
      }

      // Arama filtresi
      if (serverSearchQuery.value) {
        const query = serverSearchQuery.value.toLowerCase()
        filtered = filtered.filter(server => 
          server.server_name.toLowerCase().includes(query)
        )
      }

      return filtered
    })

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
      audioPlayer.value.volume = volume.value / 100
      audioPlayer.value.play()
      audioInitialized = true
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

    return {
      isLoading,
      connectionError,
      importantNotice,
      isDownloading,
      downloadProgress,
      news,
      background,
      menuItems,
      formatDate,
      minimizeWindow,
      closeWindow,
      checkAndStartGame,
      openSettings,
      startDrag,
      onDrag,
      stopDrag,
      currentFile,
      downloadedSize,
      totalSize,
      formatSize,
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
      getServerStatus,
      new_server,
      manifest,
      videoPlayer,
      allFilesExist,
      updateNotificationsEnabled,
      toggleUpdateNotifications,
      formatMessage,
      getSocialMediaColor,
      isPageReady,
      getLogoFontStyle,
      serverSearchQuery,
      filteredServers,
      servers,
      versionFilter,
      statusFilter,
      isVersionDropdownOpen,
      isStatusDropdownOpen,
      needsRestart,
      restartApp,
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
      isButtonsLoading
    }
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import url('http://localhost/hexlob-template/assets/css/all.min.css');

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
</style>

