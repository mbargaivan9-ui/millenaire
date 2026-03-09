/**
 * WebRTC Call Manager
 * Gère les appels audio/vidéo avec PeerJS et Reverb
 */

import Peer from 'peerjs';

class WebRTCCallManager {
    constructor(currentUserId, echo) {
        this.currentUserId = currentUserId;
        this.echo = echo;
        this.peer = null;
        this.calls = new Map(); // Appels actifs
        this.localStream = null;
        this.remoteStreams = new Map();
        this.init();
    }

    /**
     * Initialiser PeerJS
     */
    init() {
        const config = {
            host: window.location.hostname,
            port: 9000,
            path: '/peerjs',
            secure: window.location.protocol === 'https:',
            debug: window.DEBUG ? 3 : 0
        };

        this.peer = new Peer(this.currentUserId.toString(), config);

        this.peer.on('open', (id) => {
            console.log('PeerJS initialized with ID:', id);
            window.dispatchEvent(new CustomEvent('webrtc-ready'));
        });

        this.peer.on('call', (call) => {
            this.handleIncomingCall(call);
        });

        this.peer.on('error', (err) => {
            console.error('PeerJS error:', err);
            window.dispatchEvent(new CustomEvent('webrtc-error', { 
                detail: { error: err.message } 
            }));
        });

        // Écouter les événements de call via Reverb
        this.setupCallListeners();
    }

    /**
     * Configurer les écouteurs d'événements d'appel
     */
    setupCallListeners() {
        this.echo.private(`user.${this.currentUserId}`)
            .listen('CallInitiated', (data) => {
                window.dispatchEvent(new CustomEvent('incoming-call', { 
                    detail: data 
                }));
            })
            .listen('CallAnswered', (data) => {
                window.dispatchEvent(new CustomEvent('call-answered', { 
                    detail: data 
                }));
            })
            .listen('CallEnded', (data) => {
                this.endCall(data.room_id);
            });
    }

    /**
     * Initier un appel audio 1-à-1
     */
    async initiateAudioCall(recipientId, recipientPeerId) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                audio: { echoCancellation: true },
                video: false
            });

            this.localStream = stream;

            // Créer l'appel PeerJS
            const call = this.peer.call(recipientPeerId, stream);

            call.on('stream', (remoteStream) => {
                this.remoteStreams.set(recipientId, remoteStream);
                window.dispatchEvent(new CustomEvent('remote-stream', {
                    detail: { 
                        userId: recipientId, 
                        stream: remoteStream,
                        type: 'audio'
                    }
                }));
            });

            call.on('close', () => {
                this.endCall(recipientId);
            });

            call.on('error', (err) => {
                console.error('Call error:', err);
            });

            this.calls.set(recipientId, call);

            return {
                success: true,
                callId: recipientId,
                stream: stream
            };
        } catch (error) {
            console.error('Erreur lors de l\'accès au microphone:', error);
            throw error;
        }
    }

    /**
     * Initier un appel vidéo 1-à-1
     */
    async initiateVideoCall(recipientId, recipientPeerId) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                audio: { echoCancellation: true },
                video: { 
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: 'user'
                }
            });

            this.localStream = stream;

            // Créer l'appel PeerJS
            const call = this.peer.call(recipientPeerId, stream);

            call.on('stream', (remoteStream) => {
                this.remoteStreams.set(recipientId, remoteStream);
                window.dispatchEvent(new CustomEvent('remote-stream', {
                    detail: { 
                        userId: recipientId, 
                        stream: remoteStream,
                        type: 'video'
                    }
                }));
            });

            call.on('close', () => {
                this.endCall(recipientId);
            });

            call.on('error', (err) => {
                console.error('Call error:', err);
            });

            this.calls.set(recipientId, call);

            return {
                success: true,
                callId: recipientId,
                stream: stream
            };
        } catch (error) {
            console.error('Erreur lors de l\'accès à la caméra/microphone:', error);
            throw error;
        }
    }

    /**
     * Gérer un appel entrant
     */
    async handleIncomingCall(call) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                audio: true,
                video: { width: { ideal: 1280 }, height: { ideal: 720 } }
            });

            this.localStream = stream;
            call.answer(stream);

            call.on('stream', (remoteStream) => {
                this.remoteStreams.set(call.peer, remoteStream);
                window.dispatchEvent(new CustomEvent('remote-stream', {
                    detail: { 
                        userId: call.peer, 
                        stream: remoteStream,
                        type: 'video'
                    }
                }));
            });

            call.on('close', () => {
                this.endCall(call.peer);
            });

            this.calls.set(call.peer, call);

            window.dispatchEvent(new CustomEvent('call-answered-locally'));
        } catch (error) {
            console.error('Erreur lors de la réponse à l\'appel:', error);
            call.close();
        }
    }

    /**
     * Terminer un appel
     */
    async endCall(callId) {
        const call = this.calls.get(callId);
        
        if (call) {
            call.close();
            this.calls.delete(callId);
        }

        // Arrêter tous les streams
        const remoteStream = this.remoteStreams.get(callId);
        if (remoteStream) {
            remoteStream.getTracks().forEach(track => track.stop());
            this.remoteStreams.delete(callId);
        }

        window.dispatchEvent(new CustomEvent('call-ended', {
            detail: { callId }
        }));
    }

    /**
     * Arrêter la caméra/microphone local
     */
    stopLocalStream() {
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => track.stop());
            this.localStream = null;
        }
    }

    /**
     * Basculer l'audio (mute/unmute)
     */
    toggleAudio(enabled) {
        if (this.localStream) {
            this.localStream.getAudioTracks().forEach(track => {
                track.enabled = enabled;
            });
        }
    }

    /**
     * Basculer la vidéo (on/off)
     */
    toggleVideo(enabled) {
        if (this.localStream) {
            this.localStream.getVideoTracks().forEach(track => {
                track.enabled = enabled;
            });
        }
    }

    /**
     * Partager l'écran
     */
    async shareScreen() {
        try {
            const screenStream = await navigator.mediaDevices.getDisplayMedia({
                video: {
                    cursor: 'always'
                },
                audio: false
            });

            // Remplacer la vidéo de la caméra par l'écran
            if (this.localStream) {
                const videoTrack = this.localStream.getVideoTracks()[0];
                const screenVideoTrack = screenStream.getVideoTracks()[0];

                // Remplacer le track
                this.calls.forEach((call) => {
                    const sender = call.peerConnection
                        .getSenders()
                        .find(s => s.track?.kind === 'video');
                    
                    if (sender) {
                        sender.replaceTrack(screenVideoTrack);
                    }
                });

                // Arrêter la caméra
                videoTrack?.stop();

                // Écouter l'arrêt du partage d'écran
                screenVideoTrack.onended = () => {
                    this.restoreCamera();
                };
            }

            return screenStream;
        } catch (error) {
            console.error('Erreur lors du partage d\'écran:', error);
            throw error;
        }
    }

    /**
     * Retrouver la caméra après le partage d'écran
     */
    async restoreCamera() {
        try {
            const cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 1280 }, height: { ideal: 720 } }
            });

            const cameraVideoTrack = cameraStream.getVideoTracks()[0];

            this.calls.forEach((call) => {
                const sender = call.peerConnection
                    .getSenders()
                    .find(s => s.track?.kind === 'video');
                
                if (sender) {
                    sender.replaceTrack(cameraVideoTrack);
                }
            });
        } catch (error) {
            console.error('Erreur lors du retour à la caméra:', error);
        }
    }

    /**
     * Rejoindre une conférence de groupe
     */
    async joinConference(roomId, participantIds, callType = 'video') {
        try {
            const constraints = {
                audio: true,
                video: callType === 'video' ? { 
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } : false
            };

            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.localStream = stream;

            // Se connecter à chaque participant
            participantIds.forEach(peerId => {
                if (peerId !== this.currentUserId.toString()) {
                    const call = this.peer.call(peerId, stream);
                    this.calls.set(peerId, call);

                    call.on('stream', (remoteStream) => {
                        this.remoteStreams.set(peerId, remoteStream);
                        window.dispatchEvent(new CustomEvent('remote-stream', {
                            detail: { userId: peerId, stream: remoteStream }
                        }));
                    });
                }
            });

            return stream;
        } catch (error) {
            console.error('Erreur lors de la connexion à la conférence:', error);
            throw error;
        }
    }

    /**
     * Obtenir les statistiques de la connexion
     */
    async getConnectionStats(callId) {
        const call = this.calls.get(callId);
        if (!call || !call.peerConnection) return null;

        const stats = {
            audio: {},
            video: {},
            connection: {}
        };

        const report = await call.peerConnection.getStats();
        report.forEach(report => {
            if (report.type === 'inboundRtp') {
                if (report.mediaType === 'audio') {
                    stats.audio.bytesReceived = report.bytesReceived;
                    stats.audio.packetsLost = report.packetsLost;
                } else if (report.mediaType === 'video') {
                    stats.video.bytesReceived = report.bytesReceived;
                    stats.video.framesDecoded = report.framesDecoded;
                    stats.video.frameRate = report.framesPerSecond;
                }
            } else if (report.type === 'candidatePair' && report.state === 'succeeded') {
                stats.connection.currentRoundTripTime = report.currentRoundTripTime;
                stats.connection.availableOutgoingBitrate = report.availableOutgoingBitrate;
            }
        });

        return stats;
    }

    /**
     * Nettoyer et fermer
     */
    destroy() {
        // Terminer tous les appels
        this.calls.forEach((call) => {
            call.close();
        });
        this.calls.clear();

        // Arrêter tous les streams
        this.stopLocalStream();
        this.remoteStreams.forEach((stream) => {
            stream.getTracks().forEach(track => track.stop());
        });
        this.remoteStreams.clear();

        // Détruire PeerJS
        if (this.peer) {
            this.peer.destroy();
        }
    }
}

// Export pour utilisation globale
window.WebRTCCallManager = WebRTCCallManager;
