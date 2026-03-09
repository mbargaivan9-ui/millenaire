<template>
  <div class="ocr-bulletin-viewer">
    <!-- Conteneur principal -->
    <div class="ocr-container">
      <!-- Section image avec zones -->
      <div class="ocr-image-section">
        <h3>Prévisualisation du Bulletin</h3>
        
        <div v-if="!previewImage" class="no-image alert alert-warning">
          <p>⚠️ Aucune image à afficher</p>
          <small>previewImage: {{ previewImage }}</small>
        </div>
        
        <div v-else class="image-container">
          <!-- Image avec canvas pour zones -->
          <div class="image-wrapper" @mousemove="handleMouseMove" @mousedown="handleMouseDown">
            <img 
              :src="previewImage" 
              @load="onImageLoad"
              class="bulletin-image"
              alt="Bulletin scolaire"
            />
            
            <!-- Canvas pour afficher les zones -->
            <canvas 
              v-if="showZones && canvas"
              ref="zonesCanvas"
              class="zones-canvas"
              @click="handleZoneClick"
            />
            
            <!-- Overlay des zones éditables -->
            <div class="zones-overlay" v-if="showZones">
              <div 
                v-for="(zone, idx) in ocrZones"
                :key="zone.id"
                class="zone-box"
                :class="{ 'zone-selected': selectedZone === idx, 'zone-hover': hoverZone === idx }"
                :style="getZoneStyle(zone)"
                @click="selectZone(idx)"
                @mouseenter="hoverZone = idx"
                @mouseleave="hoverZone = null"
              >
                <div class="zone-text">{{ zone.text }}</div>
                <div class="zone-confidence">{{ zone.confidence }}%</div>
              </div>
            </div>
          </div>
          
          <!-- Contrôles -->
          <div class="controls">
            <button 
              @click="toggleZones"
              class="btn btn-sm"
              :class="{ 'btn-primary': showZones, 'btn-secondary': !showZones }"
            >
              {{ showZones ? '✓ Zones visibles' : 'Afficher zones' }}
            </button>
            
            <button 
              @click="zoomIn"
              class="btn btn-sm btn-secondary"
            >
              🔍+
            </button>
            
            <button 
              @click="zoomOut"
              class="btn btn-sm btn-secondary"
            >
              🔍-
            </button>
            
            <span class="zoom-level">{{ Math.round(zoomLevel * 100) }}%</span>
          </div>
        </div>
      </div>
      
      <!-- Section données OCR -->
      <div class="ocr-data-section">
        <h3>Données extraites</h3>
        
        <!-- Zone sélectionnée -->
        <div v-if="selectedZone !== null && ocrZones[selectedZone]" class="selected-zone-editor">
          <h4>Édition de zone</h4>
          <div class="form-group">
            <label>Texte détecté:</label>
            <input 
              v-model="editableZones[selectedZone]"
              type="text"
              class="form-control"
              placeholder="Texte écrit par l'OCR"
            />
            <small class="text-muted">
              Confiance: {{ ocrZones[selectedZone].confidence }}%
            </small>
          </div>
          
          <div class="form-group">
            <label>Position:</label>
            <small class="text-muted">
              X: {{ ocrZones[selectedZone].x }}, 
              Y: {{ ocrZones[selectedZone].y }}, 
              W: {{ ocrZones[selectedZone].width }}, 
              H: {{ ocrZones[selectedZone].height }}
            </small>
          </div>
          
          <button @click="applyZoneEdit" class="btn btn-sm btn-success">
            ✓ Appliquer
          </button>
          <button @click="selectedZone = null" class="btn btn-sm btn-secondary">
            ✕ Fermer
          </button>
        </div>
        
        <!-- Résumé des zones -->
        <div v-else class="zones-summary">
          <h4>{{ ocrZones.length }} zones détectées</h4>
          <div class="zones-list-scroll">
            <div 
              v-for="(zone, idx) in ocrZones"
              :key="zone.id"
              class="zone-item"
              :class="{ 'zone-highlight': hoverZone === idx }"
              @click="selectZone(idx)"
              @mouseenter="hoverZone = idx"
              @mouseleave="hoverZone = null"
            >
              <span class="zone-index">{{ idx + 1 }}</span>
              <span class="zone-value">{{ editableZones[idx] }}</span>
              <span class="zone-conf">{{ zone.confidence }}%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Boutons d'action -->
    <div class="action-buttons">
      <button @click="downloadJSON" class="btn btn-info">
        📥 Télécharger données JSON
      </button>
      <button @click="exportEditable" class="btn btn-success">
        ✓ Valider et terminer
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OCRBulletinViewer',
  props: {
    previewImage: {
      type: String,
      required: true,
    },
    ocrZones: {
      type: Array,
      default: () => [],
    },
    rawText: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      showZones: true,
      selectedZone: null,
      hoverZone: null,
      zoomLevel: 1,
      canvas: null,
      editableZones: [],
      imageWidth: 0,
      imageHeight: 0,
    };
  },
  watch: {
    ocrZones: {
      deep: true,
      handler(newZones) {
        this.editableZones = newZones.map(zone => zone.text);
        this.redrawZones();
      },
    },
  },
  methods: {
    onImageLoad(event) {
      const img = event.target;
      this.imageWidth = img.width;
      this.imageHeight = img.height;
      this.redrawZones();
    },
    
    toggleZones() {
      this.showZones = !this.showZones;
      if (this.showZones) {
        this.$nextTick(() => this.redrawZones());
      }
    },
    
    zoomIn() {
      this.zoomLevel = Math.min(this.zoomLevel + 0.2, 3);
    },
    
    zoomOut() {
      this.zoomLevel = Math.max(this.zoomLevel - 0.2, 0.5);
    },
    
    getZoneStyle(zone) {
      return {
        left: (zone.x * this.zoomLevel) + 'px',
        top: (zone.y * this.zoomLevel) + 'px',
        width: (zone.width * this.zoomLevel) + 'px',
        height: (zone.height * this.zoomLevel) + 'px',
      };
    },
    
    selectZone(idx) {
      this.selectedZone = this.selectedZone === idx ? null : idx;
    },
    
    applyZoneEdit() {
      this.$emit('zone-updated', {
        index: this.selectedZone,
        text: this.editableZones[this.selectedZone],
      });
      this.selectedZone = null;
    },
    
    redrawZones() {
      if (!this.$refs.zonesCanvas || this.ocrZones.length === 0) {
        return;
      }
      
      const canvas = this.$refs.zonesCanvas;
      canvas.width = this.imageWidth * this.zoomLevel;
      canvas.height = this.imageHeight * this.zoomLevel;
      
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      
      this.ocrZones.forEach((zone, idx) => {
        const x = zone.x * this.zoomLevel;
        const y = zone.y * this.zoomLevel;
        const w = zone.width * this.zoomLevel;
        const h = zone.height * this.zoomLevel;
        
        // Rectangle
        ctx.strokeStyle = this.selectedZone === idx ? '#ff6b6b' : '#4ecdc4';
        ctx.lineWidth = 2;
        ctx.strokeRect(x, y, w, h);
        
        // Remplissage semi-transparent
        ctx.fillStyle = this.selectedZone === idx ? 'rgba(255, 107, 107, 0.1)' : 'rgba(78, 205, 196, 0.05)';
        ctx.fillRect(x, y, w, h);
      });
    },
    
    handleMouseMove(event) {
      if (!this.showZones) return;
    },
    
    handleMouseDown(event) {
      // Pour interaction future future (drag zones, etc.)
    },
    
    handleZoneClick(event) {
      // Clic sur canvas
    },
    
    downloadJSON() {
      const data = {
        raw_text: this.rawText,
        zones_count: this.ocrZones.length,
        zones: this.ocrZones.map((zone, idx) => ({
          ...zone,
          text: this.editableZones[idx],
        })),
        exported_at: new Date().toISOString(),
      };
      
      const dataStr = JSON.stringify(data, null, 2);
      const dataBlob = new Blob([dataStr], { type: 'application/json' });
      const url = URL.createObjectURL(dataBlob);
      
      const link = document.createElement('a');
      link.href = url;
      link.download = `bulletin_ocr_${Date.now()}.json`;
      link.click();
      
      URL.revokeObjectURL(url);
    },
    
    exportEditable() {
      const editedZones = this.ocrZones.map((zone, idx) => ({
        ...zone,
        text: this.editableZones[idx],
      }));
      
      this.$emit('export-data', {
        zones: editedZones,
        text: this.editableZones.join('\n'),
      });
    },
  },
  mounted() {
    console.log('🔍 OCRBulletinViewer MOUNTED', {
      previewImage: this.previewImage,
      ocrZonesCount: this.ocrZones.length,
      rawTextLength: this.rawText?.length || 0,
    });
    this.editableZones = this.ocrZones.map(zone => zone.text);
  },
};
</script>

<style scoped>
.ocr-bulletin-viewer {
  max-width: 100%;
  background: #f8f9fa;
  border-radius: 8px;
  overflow: hidden;
}

.ocr-container {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 20px;
  padding: 20px;
  min-height: 500px;
}

.ocr-image-section h3,
.ocr-data-section h3 {
  margin: 0 0 15px 0;
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
  color: #495057;
  border-bottom: 2px solid #dee2e6;
  padding-bottom: 10px;
}

.no-image {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 300px;
  background: #e9ecef;
  border-radius: 4px;
  color: #6c757d;
  font-size: 14px;
}

.image-container {
  position: relative;
  background: white;
  border-radius: 4px;
  overflow: auto;
  max-height: 600px;
}

.image-wrapper {
  position: relative;
  display: inline-block;
  cursor: crosshair;
}

.bulletin-image {
  display: block;
  max-width: 100%;
  height: auto;
  zoom: 1;
}

.zones-canvas {
  position: absolute;
  top: 0;
  left: 0;
  display: block;
}

.zones-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.zone-box {
  position: absolute;
  border: 2px solid #4ecdc4;
  background: rgba(78, 205, 196, 0.05);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
}

.zone-box:hover {
  border-color: #36bdb1;
  background: rgba(78, 205, 196, 0.15);
}

.zone-box.zone-selected {
  border-color: #ff6b6b;
  background: rgba(255, 107, 107, 0.15);
  box-shadow: 0 0 8px rgba(255, 107, 107, 0.3);
}

.zone-text {
  font-size: 9px;
  color: #495057;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.zone-confidence {
  font-size: 8px;
  color: #999;
  margin-top: 2px;
}

.controls {
  display: flex;
  gap: 10px;
  padding: 10px;
  border-top: 1px solid #dee2e6;
  background: #f8f9fa;
  align-items: center;
}

.btn {
  padding: 6px 12px;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  background: white;
  color: #495057;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn:hover {
  border-color: #adb5bd;
  background: #e9ecef;
}

.btn-primary {
  background: #4ecdc4;
  color: white;
  border-color: #4ecdc4;
}

.btn-primary:hover {
  background: #36bdb1;
  border-color: #36bdb1;
}

.btn-secondary {
  background: white;
  color: #495057;
}

.btn-success {
  background: #51cf66;
  color: white;
  border-color: #51cf66;
}

.btn-success:hover {
  background: #40c057;
  border-color: #40c057;
}

.btn-info {
  background: #4dabf7;
  color: white;
  border-color: #4dabf7;
}

.btn-info:hover {
  background: #339af0;
  border-color: #339af0;
}

.zoom-level {
  font-size: 11px;
  color: #6c757d;
  margin-left: auto;
}

.ocr-data-section {
  border-left: 1px solid #dee2e6;
  padding-left: 15px;
}

.selected-zone-editor {
  background: #e7f5ff;
  border: 1px solid #4dabf7;
  border-radius: 4px;
  padding: 12px;
  margin-bottom: 15px;
}

.selected-zone-editor h4 {
  margin: 0 0 10px 0;
  font-size: 12px;
  font-weight: 600;
  color: #1971c2;
}

.form-group {
  margin-bottom: 10px;
}

.form-group label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  color: #495057;
  margin-bottom: 5px;
  text-transform: uppercase;
}

.form-control {
  width: 100%;
  padding: 6px 8px;
  border: 1px solid #dee2e6;
  border-radius: 3px;
  font-size: 12px;
  font-family: monospace;
}

.form-control:focus {
  outline: none;
  border-color: #4dabf7;
  box-shadow: 0 0 4px rgba(77, 171, 247, 0.3);
}

.zones-summary {
  flex: 1;
}

.zones-summary h4 {
  margin: 0 0 10px 0;
  font-size: 12px;
  font-weight: 600;
  color: #495057;
}

.zones-list-scroll {
  max-height: 450px;
  overflow-y: auto;
  border: 1px solid #dee2e6;
  border-radius: 4px;
}

.zone-item {
  padding: 8px;
  border-bottom: 1px solid #e9ecef;
  cursor: pointer;
  display: flex;
  gap: 8px;
  align-items: center;
  transition: background 0.2s ease;
  font-size: 12px;
}

.zone-item:hover {
  background: #f1f3f5;
}

.zone-item.zone-highlight {
  background: #e7f5ff;
  border-left: 3px solid #4dabf7;
  padding-left: 5px;
}

.zone-index {
  display: inline-block;
  width: 20px;
  height: 20px;
  background: #4ecdc4;
  color: white;
  border-radius: 50%;
  text-align: center;
  line-height: 20px;
  font-size: 10px;
  font-weight: 600;
  flex-shrink: 0;
}

.zone-value {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: #212529;
  font-family: monospace;
}

.zone-conf {
  font-size: 10px;
  color: #6c757d;
  flex-shrink: 0;
}

.action-buttons {
  display: flex;
  gap: 10px;
  padding: 15px 20px;
  border-top: 1px solid #dee2e6;
  background: white;
  justify-content: flex-end;
}

.text-muted {
  color: #6c757d;
  font-size: 11px;
}

@media (max-width: 1200px) {
  .ocr-container {
    grid-template-columns: 1fr;
  }
  
  .ocr-data-section {
    border-left: none;
    border-top: 1px solid #dee2e6;
    padding-left: 0;
    padding-top: 15px;
  }
}

@media (max-width: 768px) {
  .ocr-container {
    gap: 10px;
    padding: 10px;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}
</style>
