<template>
  <div class="ocr-wizard">
    <div class="container-fluid">
      <!-- En-tête du wizard -->
      <div class="wizard-header">
        <h1>📋 Assistant OCR Bulletin Scolaire</h1>
        <p class="subtitle">Uploadez le bulletin et validez les données extraites</p>
      </div>

      <!-- Étape 1: Upload -->
      <div v-if="currentStep === 'upload'" class="wizard-step">
        <div class="upload-zone" @dragover.prevent @drop="handleFileDrop">
          <input 
            type="file" 
            ref="fileInput"
            @change="handleFileSelect"
            accept="image/*,.pdf"
            style="display: none"
          />
          
          <div v-if="!selectedFile" class="upload-placeholder">
            <div class="upload-icon">📷</div>
            <h2>Déposer un fichier ici</h2>
            <p>ou <a href="#" @click.prevent="$refs.fileInput.click()">parcourir</a></p>
            <p class="upload-help">Formats supportés: JPG, PNG, PDF (max 50MB)</p>
          </div>
          
          <div v-else class="file-preview">
            <div class="file-info">
              <span class="file-icon">📄</span>
              <div>
                <strong>{{ selectedFile.name }}</strong>
                <br/>
                <small>{{ formatFileSize(selectedFile.size) }}</small>
              </div>
            </div>
            <button @click="handleFileClear" class="btn-close">✕</button>
          </div>
        </div>

        <div class="upload-actions">
          <button 
            @click="'$refs.fileInput.click()'"
            class="btn btn-secondary"
          >
            Choisir un fichier
          </button>
          
          <button 
            @click="uploadFile"
            :disabled="!selectedFile || uploading"
            class="btn btn-primary"
          >
            {{ uploading ? '⏳ Upload...' : '↑ Upload & Extraire' }}
          </button>
        </div>

        <div v-if="uploadError" class="alert alert-danger">
          {{ uploadError }}
        </div>
      </div>

      <!-- Étape 2: Validation OCR -->
      <div v-if="currentStep === 'validation' && ocrResult" class="wizard-step">
        <!-- OCRBulletinViewer component temporarily disabled -->
        <p class="text-muted">{{ ocrResult.raw_text }}</p>
      </div>

      <!-- Étape 3: Résumé -->
      <div v-if="currentStep === 'summary'" class="wizard-step">
        <div class="summary-card">
          <h2>✅ OCR complété avec succès</h2>
          
          <div class="summary-stats">
            <div class="stat">
              <span class="stat-label">Confiance OCR:</span>
              <span class="stat-value">{{ ocrResult.confidence }}%</span>
            </div>
            <div class="stat">
              <span class="stat-label">Zones détectées:</span>
              <span class="stat-value">{{ ocrResult.ocr_zones.length }}</span>
            </div>
            <div class="stat">
              <span class="stat-label">Méthode:</span>
              <span class="stat-value">{{ ocrResult.method }}</span>
            </div>
          </div>
          
          <div class="next-steps">
            <h3>Prochaines étapes:</h3>
            <ol>
              <li>Les données ont été validées et sauvegardées</li>
              <li>Vous pouvez maintenant mapper les champs aux colonnes du bulletin</li>
              <li>Configurez les formules de calcul des moyennes</li>
            </ol>
          </div>
          
          <div class="summary-actions">
            <button @click="startOver" class="btn btn-secondary">
              ← Recommencer
            </button>
            <button @click="goToMapping" class="btn btn-success">
              → Mapper les champs →
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OCRWizard',
  components: {
  },
  data() {
    return {
      currentStep: 'upload', // upload, validation, summary
      selectedFile: null,
      uploading: false,
      uploadError: null,
      ocrResult: null,
      editedZones: [],
    };
  },
  methods: {
    handleFileDrop(event) {
      const files = event.dataTransfer.files;
      if (files.length > 0) {
        this.selectedFile = files[0];
      }
    },

    handleFileSelect(event) {
      this.selectedFile = event.target.files[0];
    },

    handleFileClear() {
      this.selectedFile = null;
      this.$refs.fileInput.value = '';
    },

    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },

    async uploadFile() {
      if (!this.selectedFile) return;

      this.uploading = true;
      this.uploadError = null;

      try {
        const formData = new FormData();
        formData.append('file', this.selectedFile);

        // Get the Bearer token from localStorage or axios defaults
        const token = localStorage.getItem('auth_token') || window.axios?.defaults?.headers?.common['Authorization']?.replace('Bearer ', '');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const headers = {
          'Accept': 'application/json',
        };

        // Add CSRF token if available
        if (csrfToken) {
          headers['X-CSRF-TOKEN'] = csrfToken;
        }

        // Add Bearer token if available
        if (token) {
          headers['Authorization'] = `Bearer ${token}`;
        }

        const response = await fetch('/api/v1/teacher/bulletin/ocr/upload', {
          method: 'POST',
          headers: headers,
          body: formData,
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
          throw new Error(data.message || 'Erreur lors de l\'extraction OCR');
        }

        console.log('✅ OCR Upload Success', {
          preview_url: data.preview_url,
          ocr_zones_count: data.ocr_zones?.length || 0,
          confidence: data.confidence,
          method: data.method,
        });

        this.ocrResult = data;
        this.editedZones = data.ocr_zones.map(z => z.text);
        this.currentStep = 'validation';
        
        console.log('✅ currentStep changed to:', this.currentStep);
        console.log('✅ ocrResult set:', this.ocrResult);

      } catch (error) {
        this.uploadError = error.message;
        console.error('Upload error:', error);
      } finally {
        this.uploading = false;
      }
    },

    handleZoneUpdate(data) {
      if (this.editedZones[data.index]) {
        this.editedZones[data.index] = data.text;
      }
    },

    handleExportData(data) {
      // Sauvegarder les données et avancer
      this.ocrResult.edited_zones = data.zones;
      this.ocrResult.edited_text = data.text;
      this.currentStep = 'summary';
    },

    startOver() {
      this.currentStep = 'upload';
      this.selectedFile = null;
      this.ocrResult = null;
      this.editedZones = [];
      this.uploadError = null;
    },

    goToMapping() {
      // Rediriger vers la page de mapping des champs
      window.location.href = `/teacher/bulletin/ocr/mapping?upload=${this.ocrResult.id}`;
    },
  },
};
</script>

<style scoped>
.ocr-wizard {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 30px 0;
}

.container-fluid {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
}

.wizard-header {
  text-align: center;
  color: white;
  margin-bottom: 30px;
}

.wizard-header h1 {
  margin: 0 0 10px 0;
  font-size: 32px;
  font-weight: 700;
}

.subtitle {
  margin: 0;
  font-size: 16px;
  opacity: 0.9;
}

.wizard-step {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  margin-bottom: 30px;
}

/* Upload Zone */
.upload-zone {
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  padding: 40px;
  text-align: center;
  transition: all 0.3s ease;
  margin-bottom: 20px;
}

.upload-zone:hover {
  border-color: #667eea;
  background: #f8f9ff;
}

.upload-placeholder {
  cursor: pointer;
}

.upload-icon {
  font-size: 48px;
  margin-bottom: 15px;
}

.upload-placeholder h2 {
  margin: 15px 0 10px 0;
  color: #212529;
  font-size: 20px;
}

.upload-placeholder p {
  margin: 5px 0;
  color: #6c757d;
}

.upload-placeholder a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

.upload-placeholder a:hover {
  text-decoration: underline;
}

.upload-help {
  font-size: 12px;
  margin-top: 10px;
}

.file-preview {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 6px;
}

.file-info {
  display: flex;
  align-items: center;
  gap: 15px;
  flex: 1;
}

.file-icon {
  font-size: 24px;
}

.file-info strong {
  display: block;
  color: #212529;
  margin-bottom: 3px;
}

.file-info small {
  color: #6c757d;
}

.btn-close {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #6c757d;
  padding: 0 10px;
}

.btn-close:hover {
  color: #212529;
}

.upload-actions {
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn {
  padding: 10px 24px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #5568d3;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: #e9ecef;
  color: #495057;
}

.btn-secondary:hover:not(:disabled) {
  background: #dee2e6;
}

.btn-success {
  background: #51cf66;
  color: white;
}

.btn-success:hover {
  background: #40c057;
}

.alert {
  padding: 15px;
  border-radius: 6px;
  margin-top: 20px;
}

.alert-danger {
  background: #ffe0e0;
  color: #c92a2a;
  border: 1px solid #ffa8a8;
}

/* Résumé */
.summary-card {
  text-align: center;
}

.summary-card h2 {
  margin: 0 0 30px 0;
  color: #212529;
  font-size: 24px;
}

.summary-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 6px;
}

.stat-label {
  display: block;
  color: #6c757d;
  font-size: 12px;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.stat-value {
  display: block;
  color: #667eea;
  font-size: 28px;
  font-weight: 700;
}

.next-steps {
  text-align: left;
  background: #f0f7ff;
  border-left: 4px solid #667eea;
  padding: 20px;
  border-radius: 6px;
  margin-bottom: 30px;
}

.next-steps h3 {
  margin: 0 0 15px 0;
  color: #1971c2;
  font-size: 14px;
}

.next-steps ol {
  margin: 0;
  padding-left: 20px;
  color: #495057;
}

.next-steps li {
  margin-bottom: 8px;
}

.summary-actions {
  display: flex;
  gap: 15px;
  justify-content: center;
}

@media (max-width: 768px) {
  .wizard-header h1 {
    font-size: 24px;
  }

  .wizard-step {
    padding: 20px;
  }

  .upload-zone {
    padding: 30px 20px;
  }

  .summary-stats {
    grid-template-columns: 1fr;
  }
}
</style>
