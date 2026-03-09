<template>
  <!-- Layout 3 colonnes -->
  <div class="chat-container flex h-screen bg-gray-50">
    
    <!-- 🔥 COLONNE GAUCHE : Liste des conversations -->
    <div class="conversations-sidebar w-80 bg-white border-r border-gray-200 flex flex-col shadow-sm">
      
      <!-- Header avec bouton Invite/Nouveau -->
      <div class="sidebar-header p-4 border-b border-gray-200 bg-white">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-gray-800">Messages</h2>
          <button 
            @click="showNewConversationModal = true"
            class="p-2 hover:bg-gray-100 rounded-lg transition"
            title="Nouvelle conversation"
          >
            <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>

        <!-- Barre de recherche -->
        <div class="relative">
          <input 
            v-model="searchQuery"
            type="text"
            placeholder="Rechercher..."
            class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
          />
          <svg class="w-4 h-4 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <!-- Section "Recent" avec badge -->
      <div class="conversations-header px-4 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
        <span class="text-xs font-semibold text-gray-600 uppercase">Conversations</span>
        <span 
          v-if="totalUnread > 0"
          class="px-2 py-1 bg-red-500 text-white text-xs rounded-full font-semibold"
        >
          {{ totalUnread }}
        </span>
      </div>

      <!-- Liste des conversations -->
      <div class="conversations-list flex-1 overflow-y-auto">
        <div 
          v-if="filteredConversations.length === 0"
          class="p-4 text-center text-gray-500 text-sm"
        >
          Aucune conversation
        </div>

        <div 
          v-for="conversation in filteredConversations" 
          :key="conversation.id"
          @click="selectConversation(conversation)"
          class="conversation-item flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 transition"
          :class="{ 'bg-blue-50 border-l-4 border-blue-500': conversation.id === activeConversation?.id }"
        >
          <!-- Avatar avec statut online -->
          <div class="relative flex-shrink-0">
            <img 
              :src="getConversationAvatar(conversation)" 
              class="w-12 h-12 rounded-full object-cover"
            />
            <span 
              v-if="conversation.isOnline"
              class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"
            ></span>
          </div>

          <!-- Info conversation -->
          <div class="flex-1 ml-3 min-w-0">
            <div class="flex items-center justify-between mb-1">
              <h3 class="text-sm font-semibold text-gray-800 truncate">
                {{ getConversationName(conversation) }}
              </h3>
              <span class="text-xs text-gray-500 flex-shrink-0">{{ formatTime(conversation.last_message_at) }}</span>
            </div>
            <p class="text-xs text-gray-600 truncate">{{ getLastMessage(conversation) }}</p>
          </div>

          <!-- Badge non-lu -->
          <span 
            v-if="getUnreadCount(conversation) > 0"
            class="ml-2 px-2 py-1 bg-red-500 text-white text-xs rounded-full font-semibold flex-shrink-0"
          >
            {{ getUnreadCount(conversation) }}
          </span>
        </div>
      </div>
    </div>

    <!-- 🔥 COLONNE CENTRALE : Zone de chat active -->
    <div class="chat-area flex-1 flex flex-col bg-white">
      
      <template v-if="activeConversation">
        <!-- Header Chat Detail -->
        <div class="chat-header flex items-center justify-between p-4 bg-white border-b border-gray-200 shadow-sm">
          <div class="flex items-center gap-3">
            <img 
              :src="getConversationAvatar(activeConversation)"
              class="w-10 h-10 rounded-full"
            />
            <div>
              <h2 class="text-base font-semibold text-gray-800">
                {{ getConversationName(activeConversation) }}
              </h2>
              <p class="text-xs text-gray-500">{{ getParticipantsText(activeConversation) }}</p>
            </div>
          </div>

          <!-- Actions (call, video, info) -->
          <div class="flex items-center gap-2">
            <button 
              @click="showChatDetails = !showChatDetails"
              class="p-2 hover:bg-gray-100 rounded-lg transition"
              title="Détails"
            >
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Messages zone -->
        <div 
          ref="messagesContainer"
          class="messages-container flex-1 overflow-y-auto p-6 space-y-3"
        >
          <!-- Typing indicator -->
          <div v-if="isTyping" class="flex items-center gap-2 text-gray-600 text-sm">
            <div class="typing-dots flex gap-1">
              <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
              <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
              <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
            </div>
            <span>{{ typingUser }} est en train d'écrire...</span>
          </div>

          <!-- Messages -->
          <div 
            v-for="message in messages" 
            :key="message.id"
            class="message-wrapper"
            :class="message.sender_id === currentUserId ? 'flex justify-end' : 'flex justify-start'"
          >
            <div 
              class="message-bubble max-w-xs"
              :class="message.sender_id === currentUserId 
                ? 'bg-blue-500 text-white rounded-2xl rounded-tr-none' 
                : 'bg-gray-200 text-gray-900 rounded-2xl rounded-tl-none'
              "
            >
              <!-- Message texte -->
              <div v-if="message.type === 'text'" class="px-4 py-2">
                <p class="text-sm whitespace-pre-wrap break-words">{{ message.content }}</p>
              </div>

              <!-- Message image -->
              <div v-if="message.type === 'image' && message.attachments?.length" class="p-1">
                <img 
                  :src="message.attachments[0].url" 
                  class="max-w-full rounded-lg cursor-pointer hover:opacity-90 max-h-64"
                />
              </div>

              <!-- Timestamp -->
              <div class="px-4 py-1 flex items-center justify-end gap-1">
                <span class="text-xs" :class="message.sender_id === currentUserId ? 'text-blue-100' : 'text-gray-500'">
                  {{ formatTime(message.created_at) }}
                </span>
                <span v-if="message.sender_id === currentUserId" class="text-xs text-blue-100">
                  ✓
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Input zone -->
        <div class="input-zone p-4 bg-white border-t border-gray-200">
          <div class="flex items-center gap-3">
            <!-- Avatar utilisateur -->
            <img 
              :src="currentUser.profile_photo || '/images/default-avatar.png'" 
              class="w-10 h-10 rounded-full object-cover"
            />

            <!-- Champ de saisie -->
            <div class="flex-1 relative flex items-center gap-2">
              <input 
                v-model="messageInput"
                @input="handleTyping"
                @keyup.enter="sendMessage"
                type="text"
                placeholder="Votre message..."
                class="flex-1 px-4 py-2 bg-gray-100 rounded-full focus:ring-2 focus:ring-blue-500 focus:bg-white transition text-sm"
              />
              
              <!-- Boutons inline -->
              <button 
                @click="$refs.fileInput.click()"
                class="p-2 hover:bg-gray-100 rounded-full transition"
              >
                <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z" />
                </svg>
              </button>
              <input 
                ref="fileInput"
                type="file"
                @change="handleFileUpload"
                class="hidden"
                multiple
                accept="image/*,.pdf,.doc,.docx"
              />
            </div>

            <!-- Bouton Send -->
            <button 
              @click="sendMessage"
              :disabled="!messageInput.trim() && attachments.length === 0"
              class="p-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5.951-1.488 5.951 1.488a1 1 0 001.169-1.409l-7-14z" />
              </svg>
            </button>
          </div>

          <!-- Preview fichiers joints -->
          <div v-if="attachments.length > 0" class="mt-3 flex gap-2 flex-wrap">
            <div 
              v-for="(file, index) in attachments" 
              :key="index"
              class="relative group"
            >
              <img 
                v-if="file.type.startsWith('image')"
                :src="file.preview"
                class="w-16 h-16 object-cover rounded-lg"
              />
              <div v-else class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center text-red-500 text-xs">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a1 1 0 001 1h12a1 1 0 001-1V6a2 2 0 00-2-2H4zm0 4v4h12V8H4z" clip-rule="evenodd" />
                </svg>
              </div>
              
              <button 
                @click="removeAttachment(index)"
                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition flex items-center justify-center"
              >
                <span class="text-xs">✕</span>
              </button>
            </div>
          </div>
        </div>
      </template>

      <!-- Empty state -->
      <div v-else class="flex-1 flex items-center justify-center text-gray-500">
        <div class="text-center">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          <p class="text-lg font-medium">Sélectionnez une conversation</p>
        </div>
      </div>
    </div>

    <!-- 🔥 COLONNE DROITE : Détails (Drawer) -->
    <transition name="slide-left">
      <div 
        v-if="showChatDetails && activeConversation"
        class="chat-details-sidebar w-80 bg-white border-l border-gray-200 overflow-y-auto shadow-lg"
      >
        <!-- Chat Details Header -->
        <div class="p-4 border-b border-gray-200 sticky top-0 bg-white">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800">Détails</h3>
            <button 
              @click="showChatDetails = false"
              class="p-1 hover:bg-gray-100 rounded"
            >
              <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>

          <!-- Participants -->
          <div class="space-y-2">
            <p class="text-xs font-semibold text-gray-600 uppercase">Participants ({{ activeConversation.participants.length }})</p>
            <div class="flex flex-wrap gap-2">
              <div 
                v-for="participant in activeConversation.participants.slice(0, 4)" 
                :key="participant.id"
                class="flex items-center gap-2 px-3 py-1 bg-gray-100 rounded-full"
              >
                <img 
                  :src="participant.profile_photo || '/images/default-avatar.png'"
                  class="w-6 h-6 rounded-full"
                />
                <span class="text-xs text-gray-700">{{ participant.name }}</span>
              </div>
              <span v-if="activeConversation.participants.length > 4" class="text-xs text-gray-600 py-1">
                +{{ activeConversation.participants.length - 4 }} more
              </span>
            </div>
          </div>
        </div>

        <!-- Infos -->
        <div class="p-4 space-y-4">
          <!-- Conversation type -->
          <div>
            <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Type</p>
            <p class="text-sm text-gray-700 capitalize">{{ activeConversation.type }}</p>
          </div>

          <!-- Created -->
          <div>
            <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Créée le</p>
            <p class="text-sm text-gray-700">{{ formatDate(activeConversation.created_at) }}</p>
          </div>

          <!-- Actions -->
          <div class="pt-4 border-t border-gray-200 space-y-2">
            <button class="w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition flex items-center gap-2">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5z" />
              </svg>
              Archiver
            </button>
            <button class="w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 rounded-lg transition flex items-center gap-2">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              Supprimer
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modal : Nouvelle conversation -->
    <div v-if="showNewConversationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Nouvelle conversation</h3>
        
        <input 
          v-model="newConvSearch"
          type="text"
          placeholder="Rechercher un utilisateur..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-4"
        />

        <div class="space-y-2 max-h-64 overflow-y-auto mb-4">
          <div 
            v-for="user in availableUsers" 
            :key="user.id"
            @click="selectUserForConversation(user)"
            class="p-3 hover:bg-gray-100 rounded-lg cursor-pointer flex items-center gap-3"
            :class="{ 'bg-blue-50': selectedUsersForConv.some(u => u.id === user.id) }"
          >
            <img 
              :src="user.profile_photo || '/images/default-avatar.png'"
              class="w-10 h-10 rounded-full"
            />
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
              <p class="text-xs text-gray-500">{{ user.role }}</p>
            </div>
            <input 
              type="checkbox"
              :checked="selectedUsersForConv.some(u => u.id === user.id)"
              class="w-4 h-4 rounded"
            />
          </div>
        </div>

        <div class="flex gap-3">
          <button 
            @click="showNewConversationModal = false"
            class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
          >
            Annuler
          </button>
          <button 
            @click="createNewConversation"
            :disabled="selectedUsersForConv.length === 0"
            class="flex-1 px-4 py-2 text-white bg-blue-500 rounded-lg hover:bg-blue-600 disabled:opacity-50 transition"
          >
            Démarrer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import axios from 'axios'

// État
const conversations = ref([])
const activeConversation = ref(null)
const messages = ref([])
const messageInput = ref('')
const attachments = ref([])
const searchQuery = ref('')
const showChatDetails = ref(true)
const isTyping = ref(false)
const typingUser = ref('')
const showNewConversationModal = ref(false)
const newConvSearch = ref('')
const selectedUsersForConv = ref([])
const availableUsers = ref([])

// Utilisateur actuel
const currentUser = ref({})
const currentUserId = computed(() => currentUser.value.id)

// Total non-lus
const totalUnread = computed(() => {
  return conversations.value.reduce((sum, conv) => {
    return sum + (conv.pivot?.unread_count || 0)
  }, 0)
})

// Conversations filtrées (recherche)
const filteredConversations = computed(() => {
  if (!searchQuery.value) return conversations.value
  
  const query = searchQuery.value.toLowerCase()
  return conversations.value.filter(conv => {
    const name = getConversationName(conv).toLowerCase()
    const message = getLastMessage(conv).toLowerCase()
    return name.includes(query) || message.includes(query)
  })
})

// Refs DOM
const messagesContainer = ref(null)
const fileInput = ref(null)

// 🔥 Charger les conversations
const loadConversations = async () => {
  try {
    const { data } = await axios.get('/api/v1/chat/conversations')
    conversations.value = data.conversations
  } catch (error) {
    console.error('Erreur conversations:', error)
  }
}

// 🔥 Sélectionner une conversation
const selectConversation = async (conversation) => {
  activeConversation.value = conversation
  
  try {
    const { data } = await axios.get(`/api/v1/chat/conversations/${conversation.id}/messages`)
    messages.value = data.messages || []
    
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Erreur messages:', error)
  }
}

// 🔥 Envoyer un message
const sendMessage = async () => {
  if (!messageInput.value.trim() && attachments.value.length === 0) return
  
  const formData = new FormData()
  formData.append('content', messageInput.value)
  
  attachments.value.forEach((file, index) => {
    formData.append(`attachments[${index}]`, file.raw)
  })
  
  try {
    const { data } = await axios.post(
      `/api/v1/chat/conversations/${activeConversation.value.id}/messages`,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    )
    
    messages.value.push(data.message)
    messageInput.value = ''
    attachments.value = []
    
    await nextTick()
    scrollToBottom()
    
    // Recharger conversations pour mettre à jour le dernier message
    await loadConversations()
    
  } catch (error) {
    console.error('Erreur envoi:', error)
  }
}

// Gestion "typing indicator"
let typingTimeout
const handleTyping = () => {
  if (!activeConversation.value) return
  
  axios.post(`/api/v1/chat/conversations/${activeConversation.value.id}/typing`)
    .catch(err => console.error('Typing error:', err))
  
  clearTimeout(typingTimeout)
  typingTimeout = setTimeout(() => {}, 2000)
}

// Upload fichiers
const handleFileUpload = (event) => {
  const files = Array.from(event.target.files)
  
  files.forEach(file => {
    const reader = new FileReader()
    reader.onload = (e) => {
      attachments.value.push({
        raw: file,
        name: file.name,
        type: file.type,
        preview: e.target.result,
      })
    }
    reader.readAsDataURL(file)
  })
  
  // Réinitialiser l'input
  event.target.value = ''
}

const removeAttachment = (index) => {
  attachments.value.splice(index, 1)
}

// Helpers
const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const formatTime = (date) => {
  if (!date) return ''
  try {
    return new Date(date).toLocaleTimeString('fr-FR', {
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return ''
  }
}

const formatDate = (date) => {
  if (!date) return ''
  try {
    return new Date(date).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    })
  } catch {
    return ''
  }
}

const getConversationName = (conversation) => {
  if (conversation.type === 'group' || conversation.type === 'class') {
    return conversation.name || 'Conversation'
  }
  const other = conversation.participants.find(p => p.id !== currentUserId.value)
  return other?.name || 'Conversation'
}

const getConversationAvatar = (conversation) => {
  const other = conversation.participants.find(p => p.id !== currentUserId.value)
  return other?.profile_photo || '/images/default-avatar.png'
}

const getLastMessage = (conversation) => {
  if (!conversation.last_message) return 'Aucun message'
  return conversation.last_message.content?.substring(0, 50) || '(Fichier)'
}

const getUnreadCount = (conversation) => {
  return conversation.pivot?.unread_count || 0
}

const getParticipantsText = (conversation) => {
  const count = conversation.participants?.length || 0
  return count === 2 ? '1 participant' : `${count - 1} participants`
}

// Création nouvelle conversation
const selectUserForConversation = (user) => {
  const index = selectedUsersForConv.value.findIndex(u => u.id === user.id)
  if (index > -1) {
    selectedUsersForConv.value.splice(index, 1)
  } else {
    selectedUsersForConv.value.push(user)
  }
}

const createNewConversation = async () => {
  try {
    const userIds = selectedUsersForConv.value.map(u => u.id)
    const { data } = await axios.post('/api/v1/chat/conversations', {
      type: 'private',
      participant_ids: userIds,
    })
    
    await loadConversations()
    selectConversation(data.conversation)
    showNewConversationModal.value = false
    selectedUsersForConv.value = []
  } catch (error) {
    console.error('Erreur création conversation:', error)
  }
}

// Charger utilisateurs availables pour nouvelle conversation
const loadAvailableUsers = async () => {
  try {
    // Pour la démo, on va charger les utilisateurs via une recherche
    // Dans une vraie app, ce serait un endpoint spécifique
    const { data } = await axios.get('/api/v1/admin/users?limit=100')
    availableUsers.value = data.users.filter(u => u.id !== currentUserId.value) || []
  } catch (error) {
    console.error('Erreur chargement users:', error)
  }
}

// Initialisation
onMounted(async () => {
  // Charger utilisateur actuel
  try {
    const { data } = await axios.get('/api/v1/profile')
    currentUser.value = data.data || data
  } catch (error) {
    console.error('Erreur profile:', error)
  }
  
  // Charger conversations
  await loadConversations()
  
  // Charger utilisateurs disponibles
  await loadAvailableUsers()
})

// Watcher: Mettre à jour conversations quand newConvSearch change
watch(newConvSearch, (val) => {
  if (val) {
    // Filtrer les utilisateurs disponibles
    const query = val.toLowerCase()
    availableUsers.value = availableUsers.value.filter(u => 
      u.name.toLowerCase().includes(query)
    )
  } else {
    loadAvailableUsers()
  }
})
</script>

<style scoped>
.chat-container {
  font-family: 'Inter', 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.slide-left-enter-active,
.slide-left-leave-active {
  transition: transform 0.3s ease;
}

.slide-left-enter-from {
  transform: translateX(100%);
}

.slide-left-leave-to {
  transform: translateX(100%);
}

.conversations-list::-webkit-scrollbar,
.messages-container::-webkit-scrollbar,
.chat-details-sidebar::-webkit-scrollbar {
  width: 6px;
}

.conversations-list::-webkit-scrollbar-thumb,
.messages-container::-webkit-scrollbar-thumb,
.chat-details-sidebar::-webkit-scrollbar-thumb {
  background: #CBD5E0;
  border-radius: 3px;
}

.conversations-list::-webkit-scrollbar-thumb:hover,
.messages-container::-webkit-scrollbar-thumb:hover {
  background: #A0AEC0;
}

.animate-bounce {
  animation: bounce 1.4s infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

/* Responsive */
@media (max-width: 1024px) {
  .chat-details-sidebar {
    position: fixed;
    right: 0;
    top: 0;
    bottom: 0;
    z-index: 50;
    box-shadow: -4px 0 12px rgba(0, 0, 0, 0.15);
  }
}

@media (max-width: 768px) {
  .conversations-sidebar {
    display: none;
  }
  
  .chat-area {
    width: 100%;
  }
  
  .chat-details-sidebar {
    display: none;
  }
}
</style>
