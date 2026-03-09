<template>
  <div class="webrtc-call-interface">
    <!-- État de l'appel -->
    <div class="call-status">
      <h3>Appel en cours avec {{ recipientName }}</h3>
      <p class="call-duration">Durée: {{ callDuration }}</p>
    </div>

    <!-- Vidéos -->
    <div class="video-container">
      <!-- Vidéo locale -->
      <div class="local-video-wrapper">
        <video
          ref="localVideo"
          autoplay
          muted
          playsinline
          class="video-stream">
        </video>
        <span class="video-label">Vous</span>
      </div>

      <!-- Vidéo distante -->
      <div class="remote-video-wrapper">
        <video
          v-if="remoteStream"
          ref="remoteVideo"
          autoplay
          playsinline
          class="video-stream">
        </video>
        <span v-else class="no-video">📷 En attente de vidéo...</span>
        <span class="video-label">{{ recipientName }}</span>
      </div>
    </div>

    <!-- Contrôles d'appel -->
    <div class="call-controls">
      <!-- Microphone -->
      <button
        @click="toggleAudio"
        :class="['control-btn', { 'disabled': !audioEnabled }]"
        :title="audioEnabled ? 'Couper le micro' : 'Activer le micro'">
        {{ audioEnabled ? '🎤' : '🔇' }}
      </button>

      <!-- Caméra -->
      <button
        @click="toggleVideo"
        :class="['control-btn', { 'disabled': !videoEnabled }]"
        :title="videoEnabled ? 'Couper la caméra' : 'Activer la caméra'">
        {{ videoEnabled ? '📷' : '🚫' }}
      </button>

      <!-- Partage d'écran -->
      <button
        @click="shareScreen"
        :class="['control-btn', { 'active': isScreenSharing }]"
        title="Partager l'écran">
        🖥️
      </button>

      <!-- Statistiques de connexion -->
      <button
        @click="showStats = !showStats"
        class="control-btn"
        title="Voir les statistiques">
        📊
      </button>

      <!-- Raccrocher (bouton rouge) -->
      <button
        @click="endCall"
        class="control-btn end-call-btn"
        title="Raccrocher">
        ☎️❌
      </button>
    </div>

    <!-- Statistiques de connexion -->
    <div v-if="showStats" class="connection-stats">
      <h4>Statistiques de connexion</h4>
      <div v-if="stats" class="stats-grid">
        <div class="stat-item">
          <span class="stat-label">Délai:</span>
          <span class="stat-value">{{ (stats.connection?.currentRoundTripTime * 1000).toFixed(0) }}ms</span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Débit sortant:</span>
          <span class="stat-value">{{ (stats.connection?.availableOutgoingBitrate / 1000000).toFixed(1) }}Mbps</span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Fréquence vidéo:</span>
          <span class="stat-value">{{ stats.video?.frameRate || 0 }} fps</span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Paquets perdus (audio):</span>
          <span class="stat-value">{{ stats.audio?.packetsLost || 0 }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'WebRTCCallInterface',

  props: {
    recipientId: {
      type: Number,
      required: true
    },
    recipientName: {
      type: String,
      required: true
    },
    callType: {
      type: String,
      default: 'video',
      validator: (v) => ['audio', 'video'].includes(v)
    },
    webrtcManager: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      audioEnabled: true,
      videoEnabled: true,
      isScreenSharing: false,
      showStats: false,
      stats: null,
      callDuration: '00:00:00',
      startTime: null,
      remoteStream: null,
      statsInterval: null,
      timerInterval: null,
    };
  },

  methods: {
    /**
     * Basculer l'audio (mute/unmute)
     */
    toggleAudio() {
      this.audioEnabled = !this.audioEnabled;
      this.webrtcManager.toggleAudio(this.audioEnabled);
    },

    /**
     * Basculer la vidéo (on/off)
     */
    toggleVideo() {
      this.videoEnabled = !this.videoEnabled;
      this.webrtcManager.toggleVideo(this.videoEnabled);
    },

    /**
     * Partager l'écran
     */
    async shareScreen() {
      try {
        if (this.isScreenSharing) {
          await this.webrtcManager.restoreCamera();
          this.isScreenSharing = false;
        } else {
          await this.webrtcManager.shareScreen();
          this.isScreenSharing = true;
        }
      } catch (error) {
        console.error('Erreur lors du partage d\'écran:', error);
        alert('Erreur lors du partage d\'écran');
      }
    },

    /**
     * Terminer l'appel
     */
    async endCall() {
      if (confirm('Êtes-vous sûr de vouloir terminer cet appel?')) {
        try {
          await window.axios.post('/chat/calls/end', {
            recipient_id: this.recipientId,
            reason: 'ended'
          });

          this.webrtcManager.endCall(this.recipientId);
          this.$emit('call-ended');
        } catch (error) {
          console.error('Erreur lors de la fin d\'appel:', error);
        }
      }
    },

    /**
     * Charger les statistiques de connexion
     */
    async loadStats() {
      try {
        const stats = await this.webrtcManager.getConnectionStats(this.recipientId);
        this.stats = stats;
      } catch (error) {
        console.error('Erreur lors du chargement des stats:', error);
      }
    },

    /**
     * Mettre à jour la durée d'appel
     */
    updateCallDuration() {
      if (!this.startTime) return;

      const elapsed = new Date() - this.startTime;
      const hours = Math.floor(elapsed / 3600000);
      const minutes = Math.floor((elapsed % 3600000) / 60000);
      const seconds = Math.floor((elapsed % 60000) / 1000);

      this.callDuration = [hours, minutes, seconds]
        .map(x => String(x).padStart(2, '0'))
        .join(':');
    }
  },

  mounted() {
    // Enregistrer l'heure de début
    this.startTime = new Date();

    // Mettre à jour la durée chaque seconde
    this.timerInterval = setInterval(() => {
      this.updateCallDuration();
    }, 1000);

    // Charger les stats toutes les 2 secondes
    this.statsInterval = setInterval(() => {
      if (this.showStats) {
        this.loadStats();
      }
    }, 2000);

    // Écouter le stream distant
    window.addEventListener('remote-stream', (event) => {
      if (event.detail.userId === this.recipientId) {
        this.remoteStream = event.detail.stream;
        this.$nextTick(() => {
          if (this.$refs.remoteVideo) {
            this.$refs.remoteVideo.srcObject = event.detail.stream;
          }
        });
      }
    });

    // Assigner le stream local
    this.$nextTick(() => {
      if (this.$refs.localVideo && this.webrtcManager.localStream) {
        this.$refs.localVideo.srcObject = this.webrtcManager.localStream;
      }
    });
  },

  beforeUnmount() {
    clearInterval(this.timerInterval);
    clearInterval(this.statsInterval);

    // Nettoyer les écouteurs d'événements
    window.removeEventListener('remote-stream', this.handleRemoteStream);
  }
};
</script>

<style scoped>
.webrtc-call-interface {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: #1a1a1a;
  color: white;
  padding: 1rem;
  border-radius: 8px;
}

.call-status {
  text-align: center;
  margin-bottom: 1rem;
}

.call-status h3 {
  margin: 0;
  font-size: 1.1rem;
}

.call-duration {
  margin: 0.5rem 0 0 0;
  font-size: 0.9rem;
  color: #ccc;
}

/* Vidéos */
.video-container {
  display: flex;
  gap: 1rem;
  flex: 1;
  margin-bottom: 1rem;
  min-height: 300px;
}

.local-video-wrapper,
.remote-video-wrapper {
  flex: 1;
  position: relative;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.video-stream {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.video-label {
  position: absolute;
  bottom: 0.5rem;
  left: 0.5rem;
  background: rgba(0, 0, 0, 0.7);
  padding: 0.25rem 0.75rem;
  border-radius: 0.25rem;
  font-size: 0.85rem;
  backdrop-filter: blur(4px);
}

.no-video {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  color: #999;
  font-size: 3rem;
  background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
}

/* Contrôles d'appel */
.call-controls {
  display: flex;
  justify-content: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 8px;
  flex-wrap: wrap;
}

.control-btn {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  border: none;
  background: #0084ff;
  color: white;
  font-size: 1.25rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(0, 132, 255, 0.3);
}

.control-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 132, 255, 0.5);
}

.control-btn.disabled {
  background: #999;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.control-btn.active {
  background: #ff6b6b;
  box-shadow: 0 2px 8px rgba(255, 107, 107, 0.5);
}

.control-btn.end-call-btn {
  background: #ff4444;
  box-shadow: 0 2px 8px rgba(255, 68, 68, 0.3);
}

.control-btn.end-call-btn:hover {
  box-shadow: 0 4px 12px rgba(255, 68, 68, 0.5);
}

/* Statistiques */
.connection-stats {
  background: rgba(0, 0, 0, 0.5);
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
  backdrop-filter: blur(4px);
}

.connection-stats h4 {
  margin: 0 0 0.75rem 0;
  font-size: 0.95rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 0.75rem;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 4px;
  font-size: 0.85rem;
}

.stat-label {
  color: #ccc;
  margin-right: 1rem;
}

.stat-value {
  font-weight: 600;
  color: #0084ff;
}

/* Responsive */
@media (max-width: 768px) {
  .video-container {
    flex-direction: column;
  }

  .control-btn {
    width: 2.5rem;
    height: 2.5rem;
    font-size: 1rem;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .webrtc-call-interface {
    padding: 0.5rem;
  }

  .call-controls {
    gap: 0.5rem;
    padding: 0.75rem;
  }

  .control-btn {
    width: 2.25rem;
    height: 2.25rem;
    font-size: 0.9rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
