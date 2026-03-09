// ============================================
// CONVERSATION MANAGEMENT
// ============================================

class ConversationManager {
    constructor() {
        this.conversationId = document.querySelector('[data-conversation-id]')?.dataset.conversationId;
        this.messagesContainer = document.querySelector('[data-messages-container]');
        this.messageForm = document.querySelector('[data-message-form]');
        this.init();
    }

    init() {
        if (this.messageForm) {
            this.messageForm.addEventListener('submit', (e) => this.handleSubmit(e));
        }
        
        // Auto-scroll to bottom
        if (this.messagesContainer) {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }

        // Poll for new messages
        this.startPolling();
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        const textarea = this.messageForm.querySelector('textarea');
        const content = textarea.value.trim();
        
        if (!content) return;

        const formData = new FormData(this.messageForm);
        formData.set('conversation_id', this.conversationId);

        try {
            const response = await fetch(this.messageForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                textarea.value = '';
                this.fetchMessages();
            }
        } catch (error) {
            showToast('Erreur lors de l\'envoi du message', 'danger');
        }
    }

    async fetchMessages() {
        try {
            const response = await apiGet(`/api/conversations/${this.conversationId}/messages`);
            if (response && response.messages) {
                this.renderMessages(response.messages);
            }
        } catch (error) {
            console.error('Error fetching messages:', error);
        }
    }

    renderMessages(messages) {
        this.messagesContainer.innerHTML = '';
        
        messages.forEach(message => {
            const messageEl = document.createElement('div');
            messageEl.className = `mb-3 ${message.sender_id === userId ? 'text-end' : ''}`;
            
            messageEl.innerHTML = `
                <div class="d-inline-block" style="max-width: 70%; padding: 10px; border-radius: 10px; background-color: ${message.sender_id === userId ? '#e3f2fd' : '#f5f5f5'}">
                    <strong>${message.sender.name}</strong><br>
                    ${message.content}
                    <br>
                    <small class="text-muted">${new Date(message.created_at).toLocaleString('fr-FR')}</small>
                </div>
            `;
            
            this.messagesContainer.appendChild(messageEl);
        });
        
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    startPolling() {
        if (this.conversationId) {
            setInterval(() => this.fetchMessages(), 5000);
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('[data-conversation-id]')) {
        new ConversationManager();
    }
});
