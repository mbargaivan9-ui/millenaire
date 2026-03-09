<template>
  <div class="emoji-picker-container">
    <!-- Bouton pour ouvrir/fermer le picker -->
    <button 
      @click="showPicker = !showPicker"
      class="emoji-picker-button"
      title="Ajouter un emoji">
      😊
    </button>

    <!-- Picker d'emojis -->
    <transition name="slide">
      <div v-if="showPicker" class="emoji-picker-wrapper">
        <div class="emoji-picker-header">
          <h4>Ajouter un emoji</h4>
          <button @click="showPicker = false" class="close-btn">✕</button>
        </div>

        <!-- Catégories d'emojis populaires -->
        <div class="emoji-categories">
          <div class="category">
            <h5>Populaires</h5>
            <div class="emoji-grid">
              <button
                v-for="emoji in popularEmojis"
                :key="emoji"
                @click="selectEmoji(emoji)"
                class="emoji-btn">
                {{ emoji }}
              </button>
            </div>
          </div>

          <div class="category">
            <h5>Smileys</h5>
            <div class="emoji-grid">
              <button
                v-for="emoji in smileyEmojis"
                :key="emoji"
                @click="selectEmoji(emoji)"
                class="emoji-btn">
                {{ emoji }}
              </button>
            </div>
          </div>

          <div class="category">
            <h5>Gestes</h5>
            <div class="emoji-grid">
              <button
                v-for="emoji in gestureEmojis"
                :key="emoji"
                @click="selectEmoji(emoji)"
                class="emoji-btn">
                {{ emoji }}
              </button>
            </div>
          </div>

          <div class="category">
            <h5>Cœurs</h5>
            <div class="emoji-grid">
              <button
                v-for="emoji in heartEmojis"
                :key="emoji"
                @click="selectEmoji(emoji)"
                class="emoji-btn">
                {{ emoji }}
              </button>
            </div>
          </div>

          <div class="category">
            <h5>Symboles</h5>
            <div class="emoji-grid">
              <button
                v-for="emoji in symbolEmojis"
                :key="emoji"
                @click="selectEmoji(emoji)"
                class="emoji-btn">
                {{ emoji }}
              </button>
            </div>
          </div>
        </div>

        <!-- Champ de recherche (optionnel) -->
        <div class="emoji-search">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Chercher un emoji..."
            class="search-input">
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'EmojiPicker',
  
  data() {
    return {
      showPicker: false,
      searchQuery: '',
      
      popularEmojis: ['😄', '😂', '❤️', '🔥', '👍', '👌', '😍', '🎉', '😎', '🙌'],
      
      smileyEmojis: [
        '😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇',
        '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😚', '😙',
        '🥲', '😋', '😛', '😜', '🤪', '😌', '😑', '😐', '😶', '🤐',
        '🤨', '🤔', '🤫', '🤥', '😌', '😔', '☹️', '🙁', '😲', '😞',
      ],

      gestureEmojis: [
        '👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞',
        '🫰', '🤟', '🤘', '🤙', '👍', '👎', '✊', '👊', '🤛', '🤜',
        '👏', '🙌', '👐', '🤲', '🤝', '🤜', '🤛', '🦾', '🦿', '👋',
      ],

      heartEmojis: [
        '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔',
        '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '💜', '💥',
      ],

      symbolEmojis: [
        '✅', '❌', '❓', '❔', '❕', '⚠️', '🚀', '⭐', '✨', '💫',
        '💥', '🔔', '📢', '📣', '📯', '🔊', '🔉', '🔈', '🔇', '📻',
      ],
    };
  },

  methods: {
    /**
     * Sélectionner un emoji et l'envoyer au parent
     */
    selectEmoji(emoji) {
      this.$emit('emoji-selected', emoji);
      this.showPicker = false;
      this.searchQuery = '';
    },

    /**
     * Fermer le picker quand on clique en dehors
     */
    closeOnClickOutside(event) {
      if (!this.$el.contains(event.target)) {
        this.showPicker = false;
      }
    }
  },

  mounted() {
    document.addEventListener('click', this.closeOnClickOutside);
  },

  beforeUnmount() {
    document.removeEventListener('click', this.closeOnClickOutside);
  }
};
</script>

<style scoped>
.emoji-picker-container {
  position: relative;
  display: inline-block;
}

.emoji-picker-button {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.emoji-picker-button:hover {
  background-color: rgba(0, 0, 0, 0.1);
}

.emoji-picker-wrapper {
  position: absolute;
  bottom: 100%;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  width: 300px;
  max-height: 400px;
  overflow-y: auto;
  z-index: 1000;
  margin-bottom: 0.5rem;
}

.emoji-picker-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #eee;
  position: sticky;
  top: 0;
  background: white;
}

.emoji-picker-header h4 {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: #666;
  padding: 0;
}

.close-btn:hover {
  color: #333;
}

.emoji-categories {
  padding: 0.75rem;
}

.category {
  margin-bottom: 0.75rem;
}

.category h5 {
  margin: 0.5rem 0;
  font-size: 0.85rem;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.emoji-grid {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 4px;
}

.emoji-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.2s, transform 0.1s;
  line-height: 1;
}

.emoji-btn:hover {
  background-color: #f0f0f0;
  transform: scale(1.2);
}

.emoji-search {
  padding: 0.75rem 0.75rem 0;
  border-top: 1px solid #eee;
  position: sticky;
  bottom: 0;
  background: white;
}

.search-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
  outline: none;
}

.search-input:focus {
  border-color: #0d9488;
  box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.1);
}

/* Animation de transition */
.slide-enter-active,
.slide-leave-active {
  transition: opacity 0.2s, transform 0.2s;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

/* Scrollbar personnalisée */
.emoji-picker-wrapper::-webkit-scrollbar {
  width: 6px;
}

.emoji-picker-wrapper::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.emoji-picker-wrapper::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.emoji-picker-wrapper::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
