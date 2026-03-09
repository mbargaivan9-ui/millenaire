/**
 * Laravel Echo Configuration untuk Chat System
 * 
 * Configuration untuk real-time messaging menggunakan Pusher atau Redis
 */

import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Set up Pusher
window.Pusher = Pusher

// Initialize Laravel Echo
export default new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  encrypted: true,
  
  // Optional: Custom Pusher configuration
  client: {
    options: {
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    }
  },
  
  // Auth configuration untuk private channels
  authEndpoint: '/broadcasting/auth',
})

/**
 * METHOD UNTUK SUBSCRIBE KE CONVERSATIONS (PRIVATE CHANNEL)
 * 
 * Usage dalam Vue component:
 * Echo.private(`conversation.${conversationId}`)
 *   .listen('MessageSent', (event) => {
 *     console.log('Pesan baru:', event.message)
 *   })
 *   .listenForWhisper('typing', (event) => {
 *     console.log(event.user_name + ' sedang mengetik...')
 *   })
 */

/**
 * SETUP DI MAIN.JS
 * 
 * import Echo from '@/config/echo-config'
 * app.config.globalProperties.$echo = Echo
 */
