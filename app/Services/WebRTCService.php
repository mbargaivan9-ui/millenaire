<?php

namespace App\Services;

/**
 * WebRTCService
 * 
 * Gère la configuration et les interactions WebRTC pour les appels audio/vidéo
 * Utilise PeerJS pour les appels P2P
 */
class WebRTCService
{
    /**
     * Générer une configuration pour une salle de conférence
     */
    public function createConferenceRoom(array $participantIds, int $maxParticipants = 6): array
    {
        $roomId = 'room_' . uniqid(true);

        return [
            'room_id' => $roomId,
            'participants' => $participantIds,
            'max_participants' => $maxParticipants,
            'created_at' => now(),
            'config' => [
                'ice_servers' => [
                    [
                        'urls' => [
                            'stun:stun.l.google.com:19302',
                            'stun:stun1.l.google.com:19302',
                            'stun:stun2.l.google.com:19302',
                            'stun:stun3.l.google.com:19302',
                            'stun:stun4.l.google.com:19302',
                        ]
                    ]
                ],
                'media_constraints' => [
                    'audio' => true,
                    'video' => [
                        'width' => ['min' => 640, 'ideal' => 1280, 'max' => 1920],
                        'height' => ['min' => 480, 'ideal' => 720, 'max' => 1080],
                    ]
                ],
            ]
        ];
    }

    /**
     * Valider que l'utilisateur peut rejoindre une salle
     */
    public function canJoinRoom(int $userId, string $roomId, array $allowedUserIds): bool
    {
        return in_array($userId, $allowedUserIds);
    }

    /**
     * Générer un Peer ID unique
     */
    public function generatePeerId(): string
    {
        return 'peer_' . uniqid(true);
    }

    /**
     * Obtenir la configuration PeerJS
     */
    public function getPeerJSConfig(): array
    {
        return [
            'host' => config('app.webrtc_host', 'localhost'),
            'port' => config('app.webrtc_port', 9000),
            'path' => config('app.webrtc_path', '/peerjs'),
            'secure' => config('app.webrtc_secure', false),
            'debug' => config('app.debug', false) ? 3 : 0,
        ];
    }

    /**
     * Valider les contraintes média
     */
    public function validateMediaConstraints(array $constraints): bool
    {
        $validKeys = ['audio', 'video', 'screen'];
        
        foreach ($constraints as $key => $value) {
            if (!in_array($key, $validKeys)) {
                return false;
            }
        }

        return true;
    }
}
