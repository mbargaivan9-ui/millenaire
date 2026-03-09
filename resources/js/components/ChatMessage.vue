<template>
  <div class="chat-message" :class="[messageClass, { 'is-own': isOwnMessage, 'is-edited': message.is_edited }]">
    <!-- Avatar et info utilisateur -->
    <div v-if="!isOwnMessage" class="message-avatar">
      <img 
        :src="message.sender?.profile_photo || getAvatar(message.sender?.name)"
        :alt="message.sender?.name"
        class="avatar-img"
        :title="message.sender?.name">
    </div>

    <div class="message-content-wrapper">
      <!-- Header avec nom et timestamp -->
      <div v-if="!isOwnMessage" class="message-header">
        <span class="sender-name">{{ message.sender?.name }}</span>
        <span class="message-time">{{ formatTime(message.created_at) }}</span>
      </div>

      <!-- Contenu principal -->
      <div class="message-bubble" @click="toggleActions">
        <!-- Contenu supprimé -->
        <div v-if="message.is_deleted_for_all" class="deleted-message">
          <em>[Message supprimé]</em>
        </div>

        <!-- Texte du message -->
        <div v-else-if="isEditing" class="edit-mode">
          <textarea
            v-model="editingContent"
            class="edit-input"
            @keydown.enter.ctrl="saveEdit"
            @keydown.esc="cancelEdit">
          </textarea>
          <div class="edit-actions">
            <button @click="saveEdit" class="btn-save">Enregistrer</button>
            <button @click="cancelEdit" class="btn-cancel">Annuler</button>
          </div>
        </div>

        <p v-else class="message-text">{{ message.body || message.content }}</p>

        <!-- Pièce jointe -->
        <div v-if="message.attachments?.length" class="message-attachments">
          <div v-for="attachment in message.attachments" :key="attachment.id" class="attachment">
            <a :href="`/chat/attachments/${attachment.id}/download`" class="attachment-link">
              📎 {{ attachment.file_name }}
            </a>
          </div>
        </div>

        <!-- Indicateur d'édition -->
        <div v-if="message.is_edited" class="edited-indicator">
          <small>(modifié)</small>
        </div>
      </div>

      <!-- Actions du message (édition, suppression, emoji) -->
      <div v-show="showActions" class="message-actions">
        <!-- Ajouter un emoji réaction -->
        <button @click="showEmojiPicker = !showEmojiPicker" class="action-btn" title="Ajouter emoji">
          👍
        </button>

        <!-- Éditer (seulement pour le propriétaire) -->
        <button 
          v-if="isOwnMessage && !message.is_deleted_for_all"
          @click="startEdit"
          class="action-btn"
          title="Éditer">
          ✏️
        </button>

        <!-- Supprimer pour moi -->
        <button 
          v-if="isOwnMessage"
          @click="deleteForSender"
          class="action-btn"
          title="Supprimer pour moi">
          🗑️
        </button>

        <!-- Supprimer pour tous -->
        <button 
          v-if="isOwnMessage"
          @click="deleteForAll"
          class="action-btn"
          title="Supprimer pour tous">
          ❌
        </button>

        <!-- Voir les lecteurs -->
        <button 
          @click="showReadStatus"
          class="action-btn"
          title="Voir les statuts de lecture">
          ✓
        </button>
      </div>

      <!-- Emoji picker inline -->
      <emoji-picker 
        v-if="showEmojiPicker"
        @emoji-selected="addEmojiReaction"
        class="inline-emoji-picker"/>

      <!-- Réactions aux emojis -->
      <div v-if="message.reactions?.length" class="message-reactions">
        <div v-for="group in groupedReactions" :key="group.emoji" class="reaction-group">
          <span class="reaction-emoji">{{ group.emoji }}</span>
          <span class="reaction-count">{{ group.count }}</span>
        </div>
      </div>

      <!-- Statuts de lecture -->
      <div v-if="readStatus && isOwnMessage" class="read-status">
        <span v-if="readStatus.status === 'sent'" class="status-icon" title="Envoyé">✓✓</span>
        <span v-else-if="readStatus.status === 'read'" class="status-icon read" title="Lu">✓✓</span>
        <div v-if="showReadDetails" class="read-details">
          <div v-for="reader in readStatus.readers" :key="reader.user_id" class="reader-item">
            <img 
              :src="reader.avatar || getAvatar(reader.user_name)"
              :alt="reader.user_name"
              class="reader-avatar">
            <div class="reader-info">
              <span class="reader-name">{{ reader.user_name }}</span>
              <small class="reader-time">{{ reader.read_at }}</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Timestamp pour les messages de l'utilisateur -->
      <div v-if="isOwnMessage" class="message-footer">
        <span class="message-time">{{ formatTime(message.created_at) }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import EmojiPicker from './EmojiPicker.vue';

export default {
  name: 'ChatMessage',
  components: { EmojiPicker },

  props: {
    message: {
      type: Object,
      required: true
    },
    currentUserId: {
      type: Number,
      required: true
    }
  },

  data() {
    return {
      showActions: false,
      showEmojiPicker: false,
      showReadDetails: false,
      isEditing: false,
      editingContent: '',
      readStatus: null,
    };
  },

  computed: {
    isOwnMessage() {
      return this.message.user_id === this.currentUserId;
    },

    messageClass() {
      return {
        'message-deleted': this.message.is_deleted_for_all || this.message.is_deleted_for_sender,
      };
    },

    groupedReactions() {
      if (!this.message.reactions?.length) return [];

      const groups = {};
      this.message.reactions.forEach(reaction => {
        const emoji = reaction.emoji;
        groups[emoji] = (groups[emoji] || 0) + 1;
      });

      return Object.entries(groups).map(([emoji, count]) => ({
        emoji,
        count
      }));
    }
  },

  methods: {
    toggleActions() {
      this.showActions = !this.showActions;
    },

    startEdit() {
      this.isEditing = true;
      this.editingContent = this.message.body || this.message.content;
      this.showActions = false;
    },

    async saveEdit() {
      try {
        const response = await window.axios.put(
          `/chat/messages/${this.message.id}`,
          { body: this.editingContent }
        );

        this.message.body = this.editingContent;
        this.message.is_edited = true;
        this.message.edited_at = new Date();
        this.isEditing = false;

        this.$emit('message-edited', response.data);
      } catch (error) {
        console.error('Erreur lors de la modification:', error);
        alert('Erreur lors de la modification du message');
      }
    },

    cancelEdit() {
      this.isEditing = false;
      this.editingContent = '';
    },

    async deleteForSender() {
      if (!confirm('Supprimer ce message pour vous?')) return;

      try {
        await window.axios.post(
          `/chat/messages/${this.message.id}/delete-sender`
        );

        this.message.is_deleted_for_sender = true;
        this.$emit('message-deleted');
      } catch (error) {
        console.error('Erreur lors de la suppression:', error);
      }
    },

    async deleteForAll() {
      if (!confirm('Supprimer ce message pour tous? Cette action est irréversible.')) return;

      try {
        await window.axios.post(
          `/chat/messages/${this.message.id}/delete-all`
        );

        this.message.is_deleted_for_all = true;
        this.message.body = null;
        this.message.content = null;
        this.$emit('message-deleted');
      } catch (error) {
        console.error('Erreur lors de la suppression:', error);
      }
    },

    async addEmojiReaction(emoji) {
      try {
        await window.axios.post(
          `/chat/messages/${this.message.id}/react`,
          { emoji }
        );

        // Ajouter la réaction localement
        if (!this.message.reactions) {
          this.message.reactions = [];
        }
        this.message.reactions.push({ emoji, user_id: this.currentUserId });
        this.showEmojiPicker = false;

        this.$emit('emoji-added');
      } catch (error) {
        console.error('Erreur lors de l\'ajout de l\'emoji:', error);
      }
    },

    async showReadStatus() {
      try {
        const response = await window.axios.get(
          `/chat/messages/${this.message.id}/read-status`
        );

        this.readStatus = response.data;
        this.showReadDetails = !this.showReadDetails;
      } catch (error) {
        console.error('Erreur lors du chargement des statuts:', error);
      }
    },

    formatTime(timestamp) {
      const date = new Date(timestamp);
      const now = new Date();
      const diffInMinutes = Math.floor((now - date) / 60000);

      if (diffInMinutes < 1) return 'À l\'instant';
      if (diffInMinutes < 60) return `il y a ${diffInMinutes}min`;
      if (diffInMinutes < 1440) {
        const hours = Math.floor(diffInMinutes / 60);
        return `il y a ${hours}h`;
      }

      return date.toLocaleDateString('fr-FR', { 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    getAvatar(name) {
      const initials = name?.split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase() || 'U';

      return `https://ui-avatars.com/api/?name=${encodeURIComponent(initials)}&background=0d9488&color=fff`;
    }
  },

  mounted() {
    // Charger les statuts de lecture au montage
    if (this.isOwnMessage) {
      this.showReadStatus().catch(() => {});
    }
  }
};
</script>

<style scoped>
.chat-message {
  display: flex;
  margin-bottom: 1rem;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.chat-message.is-own {
  flex-direction: row-reverse;
}

.message-avatar {
  width: 2.5rem;
  height: 2.5rem;
  margin-right: 0.75rem;
  flex-shrink: 0;
}

.avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.chat-message.is-own .message-avatar {
  margin-right: 0;
  margin-left: 0.75rem;
}

.message-content-wrapper {
  flex: 1;
  max-width: 70%;
}

.chat-message.is-own .message-content-wrapper {
  align-items: flex-end;
}

.message-header {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 0.25rem;
  font-size: 0.85rem;
}

.sender-name {
  font-weight: 600;
  color: #333;
}

.message-time {
  color: #999;
  font-size: 0.8rem;
}

.message-bubble {
  background-color: #e5e5ea;
  padding: 0.75rem 1rem;
  border-radius: 1rem;
  word-wrap: break-word;
  position: relative;
  cursor: pointer;
  transition: background-color 0.2s;
}

.chat-message.is-own .message-bubble {
  background-color: #0084ff;
  color: white;
}

.message-bubble:hover {
  background-color: #d9d9e3;
}

.chat-message.is-own .message-bubble:hover {
  background-color: #0073e6;
}

.message-text {
  margin: 0;
  word-break: break-word;
  line-height: 1.3;
}

.deleted-message {
  color: #999;
  font-style: italic;
}

.message-deleted .message-bubble {
  opacity: 0.6;
}

.edited-indicator {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: #999;
}

.chat-message.is-own .edited-indicator {
  color: rgba(255, 255, 255, 0.7);
}

/* Mode édition */
.edit-mode {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.edit-input {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 0.25rem;
  font-family: inherit;
  resize: vertical;
  min-height: 3rem;
}

.edit-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.btn-save, .btn-cancel {
  padding: 0.25rem 0.75rem;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
}

.btn-save {
  background-color: #0084ff;
  color: white;
}

.btn-cancel {
  background-color: #e5e5ea;
  color: #333;
}

/* Actions du message */
.message-actions {
  display: flex;
  gap: 0.25rem;
  margin-top: 0.5rem;
  padding: 0.25rem;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 0.5rem;
}

.action-btn {
  background: none;
  border: none;
  font-size: 1rem;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  transition: background-color 0.2s;
}

.action-btn:hover {
  background-color: rgba(0, 0, 0, 0.1);
}

/* Emoji picker */
.inline-emoji-picker {
  margin-top: 0.5rem;
}

/* Réactions */
.message-reactions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
  margin-top: 0.5rem;
}

.reaction-group {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  background: rgba(0, 0, 0, 0.05);
  padding: 0.25rem 0.5rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
}

.reaction-emoji {
  font-size: 1rem;
}

.reaction-count {
  color: #666;
  font-weight: 600;
}

/* Statuts de lecture */
.read-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
  font-size: 0.85rem;
}

.status-icon {
  color: #999;
  font-weight: bold;
}

.status-icon.read {
  color: #0084ff;
}

.read-details {
  position: absolute;
  bottom: 100%;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 0.5rem;
  padding: 0.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  min-width: 200px;
  z-index: 100;
}

.reader-item {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  align-items: center;
}

.reader-avatar {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  object-fit: cover;
}

.reader-info {
  display: flex;
  flex-direction: column;
  font-size: 0.85rem;
}

.reader-name {
  font-weight: 600;
  color: #333;
}

.reader-time {
  color: #999;
}

/* Pièces jointes */
.message-attachments {
  margin-top: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.attachment {
  background: rgba(0, 0, 0, 0.1);
  padding: 0.5rem;
  border-radius: 0.25rem;
}

.attachment-link {
  color: inherit;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.chat-message.is-own .attachment-link {
  color: white;
}

« Footer »
.message-footer {
  display: flex;
  margin-top: 0.25rem;
  font-size: 0.85rem;
}
</style>
