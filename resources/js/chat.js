/**
 * Chat System — JavaScript Principal
 * Gère le chat intégral avec messages, présence, appels WebRTC
 */

import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import Peer from 'peerjs';

class ChatManager {
    constructor() {
        this.currentConversation = null;
        this.messages = [];
        this.typingUsers = new Map();
        this.onlineUsers = new Map();
        this.currentCall = null;
        this.peer = null;
        this.webrtcConfig = null;
        this.currentUserId = null;
        this.echo = null;
        this.init();
    }

    /**
     * Initialiser le chat
     */
    async init() {
        this.currentUserId = document.querySelector('meta[name="user-id"]')?.content;
        
        // Initialiser Echo pour la broadcast en temps réel
        this.setupEcho();
        
        // Initialiser PeerJS
        this.setupWebRTC();
        
        // Charger les conversations
        await this.loadConversations();
        
        // Mettre à jour le statut en ligne
        this.markUserOnline();
        
        // Écouter les changements
        this.setupListeners();
    }

    /**
     * Configurer Laravel Echo pour les broadcasts
     */
    setupEcho() {
        // Configuration de l'Echo avec Reverb ou Pusher
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: document.querySelector('meta[name="pusher-key"]')?.content,
            wsHost: document.querySelector('meta[name="pusher-host"]')?.content,
            wsPort: document.querySelector('meta[name="pusher-port"]')?.content || 80,
            wssPort: document.querySelector('meta[name="pusher-port"]')?.content || 443,
            forceTLS: (document.querySelector('meta[name="pusher-port"]')?.content || 443) === 443,
            encrypted: true,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
        });

        this.echo = window.Echo;
    }

    /**
     * Configurer PeerJS pour les appels WebRTC
     */
    setupWebRTC() {
        const config = {
            host: window.location.hostname,
            port: 9000,
            path: '/peerjs',
            secure: window.location.protocol === 'https:',
            debug: 3
        };

        this.peer = new Peer(this.currentUserId.toString(), config);

        this.peer.on('open', (id) => {
            console.log('PeerJS ready with ID:', id);
        });

        this.peer.on('call', (call) => {
            this.handleIncomingCall(call);
        });

        this.peer.on('error', (err) => {
            console.error('PeerJS error:', err);
        });
    }

    /**
     * Charger les conversations de l'utilisateur
     */
    async loadConversations() {
        try {
            const response = await axios.get('/chat/conversations');
            return response.data;
        } catch (error) {
            console.error('Erreur lors du chargement des conversations:', error);
        }
    }

    /**
     * Charger les messages d'une conversation
     */
    async loadMessages(conversationId) {
        try {
            const response = await axios.get(`/chat/conversations/${conversationId}`);
            this.currentConversation = conversationId;
            this.messages = response.data.messages;
            
            // Marquer les messages comme lus
            this.markConversationAsRead(conversationId);
            
            return response.data;
        } catch (error) {
            console.error('Erreur lors du chargement des messages:', error);
        }
    }

    /**
     * Envoyer un message
     */
    async sendMessage(conversationId, content, type = 'text') {
        try {
            const response = await axios.post('/chat/messages', {
                conversation_id: conversationId,
                body: content,
                type: type,
            });

            // Ajouter le message localement
            this.messages.push(response.data.message);
            
            return response.data;
        } catch (error) {
            console.error('Erreur lors de l\'envoi du message:', error);
        }
    }

    /**
     * Éditer un message
     */
    async editMessage(messageId, newContent) {
        try {
            const response = await axios.put(`/chat/messages/${messageId}`, {
                body: newContent,
            });

            // Mettre à jour localement
            const message = this.messages.find(m => m.id === messageId);
            if (message) {
                message.body = newContent;
                message.is_edited = true;
                message.edited_at = new Date();
            }

            return response.data;
        } catch (error) {
            console.error('Erreur lors de la modification:', error);
        }
    }

    /**
     * Supprimer un message pour l'expéditeur
     */
    async deleteMessageForSender(messageId) {
        try {
            const response = await axios.post(`/chat/messages/${messageId}/delete-sender`);
            
            const message = this.messages.find(m => m.id === messageId);
            if (message) {
                message.is_deleted_for_sender = true;
            }

            return response.data;
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
        }
    }

    /**
     * Supprimer un message pour tout le monde
     */
    async deleteMessageForAll(messageId) {
        try {
            const response = await axios.post(`/chat/messages/${messageId}/delete-all`);
            
            const message = this.messages.find(m => m.id === messageId);
            if (message) {
                message.is_deleted_for_all = true;
                message.body = null;
            }

            return response.data;
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
        }
    }

    /**
     * Marquer un message comme lu
     */
    async markMessageAsRead(messageId) {
        try {
            await axios.post(`/chat/messages/${messageId}/read`);
        } catch (error) {
            console.error('Erreur lors de la marque de lecture:', error);
        }
    }

    /**
     * Obtenir les statuts de lecture d'un message
     */
    async getReadStatus(messageId) {
        try {
            const response = await axios.get(`/chat/messages/${messageId}/read-status`);
            return response.data;
        } catch (error) {
            console.error('Erreur lors de la récupération du statut:', error);
        }
    }

    /**
     * Marquer la conversation comme lue
     */
    async markConversationAsRead(conversationId) {
        try {
            await axios.post(`/chat/conversations/${conversationId}/read`);
        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    /**
     * Envoyer un indicateur de saisie
     */
    async sendTypingIndicator(conversationId) {
        try {
            await axios.post('/chat/typing', {
                conversation_id: conversationId,
            });
        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    /**
     * Créer une nouvelle conversation
     */
    async createConversation(participantIds, name = null, type = 'direct') {
        try {
            const response = await axios.post('/chat/conversations', {
                participant_ids: participantIds,
                name: name,
                type: type,
            });

            return response.data;
        } catch (error) {
            console.error('Erreur lors de la création:', error);
        }
    }

    /**
     * Chercher des contacts autorisés
     */
    async searchContacts(query) {
        try {
            const response = await axios.get('/chat/search-users', {
                params: { search: query }
            });

            return response.data;
        } catch (error) {
            console.error('Erreur lors de la recherche:', error);
        }
    }

    // ─── APPELS WEBRTC ──────────────────────────────────────────

    /**
     * Initier un appel audio
     */
    async initiateAudioCall(recipientId) {
        try {
            const response = await axios.post('/chat/calls/initiate', {
                recipient_id: recipientId,
                call_type: 'audio',
            });

            this.currentCall = response.data;
            return response.data;
        } catch (error) {
            console.error('Erreur lors de l\'appel:', error);
        }
    }

    /**
     * Initier un appel vidéo
     */
    async initiateVideoCall(recipientId) {
        try {
            const response = await axios.post('/chat/calls/initiate', {
                recipient_id: recipientId,
                call_type: 'video',
            });

            this.currentCall = response.data;
            return response.data;
        } catch (error) {
            console.error('Erreur lors de l\'appel:', error);
        }
    }

    /**
     * Répondre à un appel
     */
    async answerCall(callerId, roomId) {
        try {
            const response = await axios.post('/chat/calls/answer', {
                caller_id: callerId,
                room_id: roomId,
            });

            return response.data;
        } catch (error) {
            console.error('Erreur lors de la réponse:', error);
        }
    }

    /**
     * Terminer un appel
     */
    async endCall(recipientId, reason = 'ended') {
        try {
            const response = await axios.post('/chat/calls/end', {
                recipient_id: recipientId,
                reason: reason,
            });

            this.currentCall = null;
            return response.data;
        } catch (error) {
            console.error('Erreur lors de la fin d\'appel:', error);
        }
    }

    /**
     * Gérer un appel entrant
     */
    handleIncomingCall(call) {
        console.log('Appel entrant de:', call.peer);
        
        // Dispatcher un événement personnalisé
        window.dispatchEvent(new CustomEvent('incoming-call', {
            detail: {
                peer: call.peer,
                call: call,
                type: 'audio'
            }
        }));
    }

    /**
     * Accepter un appel WebRTC
     */
    acceptCall(call) {
        navigator.mediaDevices.getUserMedia({ audio: true, video: false })
            .then((stream) => {
                call.answer(stream);
                
                call.on('stream', (remoteStream) => {
                    window.dispatchEvent(new CustomEvent('call-stream', {
                        detail: { stream: remoteStream, type: 'audio' }
                    }));
                });
            })
            .catch((err) => {
                console.error('Erreur d\'accès au microphone:', err);
            });
    }

    /**
     * Rejoindre une conférence vidéo
     */
    async joinConference(roomId, callType = 'video') {
        try {
            const constraints = {
                audio: true,
                video: callType === 'video' ? { width: 1280, height: 720 } : false
            };

            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            
            // Dispatcher l'événement avec le stream
            window.dispatchEvent(new CustomEvent('conference-joined', {
                detail: {
                    room_id: roomId,
                    stream: stream,
                    call_type: callType
                }
            }));

            return stream;
        } catch (error) {
            console.error('Erreur lors de la connexion à la conférence:', error);
        }
    }

    // ─── PRÉSENCE ──────────────────────────────────────────────

    /**
     * Marquer l'utilisateur en ligne
     */
    async markUserOnline() {
        try {
            await axios.post('/chat/status/online', {
                is_online: true,
            });

            // Émettre l'événement
            this.echo.channel('presence').listen('UserOnlineStatusChanged', (data) => {
                this.onlineUsers.set(data.user_id, {
                    name: data.user_name,
                    is_online: data.is_online,
                    last_login: data.last_login,
                });

                window.dispatchEvent(new CustomEvent('user-status-changed', { detail: data }));
            });
        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    /**
     * Marquer l'utilisateur hors ligne
     */
    async markUserOffline() {
        try {
            await axios.post('/chat/status/online', {
                is_online: false,
            });
        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    /**
     * Configurer les écouteurs d'événements
     */
    setupListeners() {
        if (!this.echo) return;

        // Écouter les messages en temps réel
        this.echo.channel('conversation.' + this.currentConversation)
            .listen('MessageSent', (data) => {
                this.messages.push(data.message);
                window.dispatchEvent(new CustomEvent('message-received', { detail: data }));
            })
            .listen('MessageEdited', (data) => {
                const message = this.messages.find(m => m.id === data.message_id);
                if (message) {
                    message.body = data.content;
                    message.is_edited = true;
                    message.edited_at = data.edited_at;
                }
                window.dispatchEvent(new CustomEvent('message-edited', { detail: data }));
            })
            .listen('MessageRead', (data) => {
                window.dispatchEvent(new CustomEvent('message-read', { detail: data }));
            })
            .listen('UserTyping', (data) => {
                this.typingUsers.set(data.user_id, data.user_name);
                window.dispatchEvent(new CustomEvent('user-typing', { detail: data }));
                
                setTimeout(() => {
                    this.typingUsers.delete(data.user_id);
                }, 3000);
            });

        // Écouter les appels entrants
        this.echo.private('user.' + this.currentUserId)
            .listen('CallInitiated', (data) => {
                window.dispatchEvent(new CustomEvent('call-initiated', { detail: data }));
            })
            .listen('CallAnswered', (data) => {
                window.dispatchEvent(new CustomEvent('call-answered', { detail: data }));
            })
            .listen('CallEnded', (data) => {
                window.dispatchEvent(new CustomEvent('call-ended', { detail: data }));
            });
    }

    /**
     * Nettoyer lors de la fermeture
     */
    async cleanup() {
        await this.markUserOffline();
        if (this.peer) {
            this.peer.destroy();
        }
        if (this.echo) {
            this.echo.leaveAllChannels();
        }
    }
}

// Export pour utilisation globale
window.ChatManager = ChatManager;

// Initialiser au chargement
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.chatManager = new ChatManager();
    });
} else {
    window.chatManager = new ChatManager();
}

// Nettoyer à la fermeture
window.addEventListener('beforeunload', () => {
    if (window.chatManager) {
        window.chatManager.cleanup();
    }
});
