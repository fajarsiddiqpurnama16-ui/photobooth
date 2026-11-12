export class SoundManager {
  private ctx: AudioContext | null = null;
  private _enabled = false;
  private shutterBuffer: AudioBuffer | null = null;

  get enabled() {
    return this._enabled;
  }

  get muted() {
    return !this._enabled;
  }

  setEnabled(v: boolean) {
    this._enabled = Boolean(v);
    if (this._enabled) {
      this.ensureCtx();
    }
  }

  setMuted(isMuted: boolean) {
    this.setEnabled(!isMuted);
  }

  toggle() {
    this.setEnabled(!this._enabled);
    return this._enabled;
  }

  private ensureCtx(): AudioContext | null {
    if (typeof window === 'undefined') return null;
    try {
      if (!this.ctx) {
        const AC =
          window.AudioContext ||
          (window as unknown as { webkitAudioContext: typeof AudioContext }).webkitAudioContext;
        if (!AC) return null;
        this.ctx = new AC();
      }
      if (this.ctx && this.ctx.state === 'suspended') {
        void this.ctx.resume().catch(() => {});
      }
      return this.ctx;
    } catch (e) {
      console.warn('AudioContext error:', e);
      return null;
    }
  }

  private getShutterBuffer(ctx: AudioContext): AudioBuffer {
    if (this.shutterBuffer && this.shutterBuffer.sampleRate === ctx.sampleRate) {
      return this.shutterBuffer;
    }
    const dur = 0.12;
    const sr = ctx.sampleRate;
    const buf = ctx.createBuffer(1, Math.floor(sr * dur), sr);
    const d = buf.getChannelData(0);
    for (let i = 0; i < d.length; i++) {
      const t = i / d.length;
      d[i] = (Math.random() * 2 - 1) * Math.pow(1 - t, 2.5);
    }
    this.shutterBuffer = buf;
    return buf;
  }

  shutter() {
    if (!this._enabled) return;
    try {
      const ctx = this.ensureCtx();
      if (!ctx) return;
      const buf = this.getShutterBuffer(ctx);
      const src = ctx.createBufferSource();
      src.buffer = buf;
      const g = ctx.createGain();
      g.gain.value = 0.22;
      const hp = ctx.createBiquadFilter();
      hp.type = 'highpass';
      hp.frequency.value = 1800;
      src.connect(hp).connect(g).connect(ctx.destination);
      src.start();
    } catch (e) {
      console.warn('Shutter sound error:', e);
    }
  }

  beep(high = false) {
    if (!this._enabled) return;
    try {
      const ctx = this.ensureCtx();
      if (!ctx) return;
      const osc = ctx.createOscillator();
      const g = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.value = high ? 1320 : 880;
      const now = ctx.currentTime;
      g.gain.setValueAtTime(0.0001, now);
      g.gain.exponentialRampToValueAtTime(0.15, now + 0.01);
      g.gain.exponentialRampToValueAtTime(0.0001, now + 0.18);
      osc.connect(g).connect(ctx.destination);
      osc.start(now);
      osc.stop(now + 0.2);
    } catch (e) {
      console.warn('Beep sound error:', e);
    }
  }

  click() {
    if (!this._enabled) return;
    try {
      const ctx = this.ensureCtx();
      if (!ctx) return;
      const osc = ctx.createOscillator();
      const g = ctx.createGain();
      osc.type = 'triangle';
      osc.frequency.value = 1600;
      const now = ctx.currentTime;
      g.gain.setValueAtTime(0.08, now);
      g.gain.exponentialRampToValueAtTime(0.0001, now + 0.04);
      osc.connect(g).connect(ctx.destination);
      osc.start(now);
      osc.stop(now + 0.05);
    } catch (e) {
      console.warn('Click sound error:', e);
    }
  }
}

export const sound = new SoundManager();
