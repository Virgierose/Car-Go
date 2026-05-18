<style>
@import url('https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@300;400;500&display=swap');
*, *::before, *::after { box-sizing: border-box; }
:root {
  --crimson: #c0392b;
  --crimson-soft: #e05252;
  --crimson-glow: rgba(192,57,43,0.18);
  --bg-deep: #0a0a0b;
  --bg-card: #111113;
  --bg-input: #18181c;
  --border: rgba(255,255,255,0.07);
  --border-focus: rgba(192,57,43,0.5);
  --text-primary: #f0f0f0;
  --text-muted: #666;
  --text-label: #999;
}
body { background: var(--bg-deep); color: var(--text-primary); font-family: 'Barlow', sans-serif; }
.bk-hero { background: linear-gradient(135deg, #0a0a0b 60%, #1a0808); border-bottom: 1px solid var(--border); padding: 3rem 2rem 2rem; text-align: center; }
.bk-label { font-family: 'Barlow Condensed', sans-serif; font-size: .72rem; letter-spacing: .22em; text-transform: uppercase; color: var(--crimson-soft); display: block; margin-bottom: .6rem; }
.bk-title { font-family: 'Barlow Condensed', sans-serif; font-size: 2.8rem; font-weight: 800; text-transform: uppercase; margin: 0 0 .4rem; }
.bk-sub { font-size: .85rem; color: var(--text-muted); }
.steps-wrap { padding: 2rem 2rem 0; max-width: 720px; margin: 0 auto; }
.steps { display: flex; align-items: center; }
.step { display: flex; flex-direction: column; align-items: center; gap: .3rem; min-width: 52px; }
.step-num { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Barlow Condensed', sans-serif; font-weight: 700; font-size: .85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-muted); transition: all .3s; }
.step.active .step-num { background: var(--crimson); border-color: var(--crimson); color: #fff; box-shadow: 0 0 14px var(--crimson-glow); }
.step.done .step-num { background: transparent; border-color: var(--crimson); color: var(--crimson-soft); }
.step-label { font-size: .6rem; letter-spacing: .12em; text-transform: uppercase; color: var(--text-muted); }
.step.active .step-label { color: var(--crimson-soft); }
.step.done .step-label { color: var(--crimson-soft); opacity: .7; }
.step-line { flex: 1; height: 1px; background: var(--border); margin: 0 4px; position: relative; top: -10px; }
.step-line.done { background: var(--crimson); opacity: .4; }
.bk-section { padding: 2rem; }
.bk-container { max-width: 720px; margin: 0 auto; }
.flow-card { background: rgba(17,17,19,0.9); border: 1px solid var(--border); padding: 2.5rem; backdrop-filter: blur(10px); animation: fadeUp .4s ease; }
@keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
.flow-card h2 { font-family: 'Barlow Condensed', sans-serif; font-size: 1.4rem; text-transform: uppercase; letter-spacing: .08em; margin: 0 0 1.8rem; color: var(--text-primary); }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
@media(max-width:540px){ .grid-2 { grid-template-columns: 1fr; } }
.form-group { display: flex; flex-direction: column; gap: .4rem; margin-bottom: .2rem; }
.form-label { font-size: .68rem; letter-spacing: .18em; text-transform: uppercase; color: var(--text-label); }
.form-control { padding: 11px 14px; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-primary); font-family: 'Barlow', sans-serif; font-size: .92rem; width: 100%; transition: border-color .2s, box-shadow .2s; }
.form-control:focus { outline: none; border-color: var(--border-focus); box-shadow: 0 0 0 3px var(--crimson-glow); }
.form-control option { background: #1a1a1e; }
textarea.form-control { resize: vertical; }
.bk-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; flex-wrap: wrap; }
.btn { padding: 11px 26px; font-family: 'Barlow Condensed', sans-serif; font-size: .9rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; text-decoration: none; cursor: pointer; border: none; transition: all .2s; display: inline-flex; align-items: center; gap: .5rem; }
.btn-ghost { background: transparent; border: 1px solid var(--border); color: var(--text-muted); }
.btn-ghost:hover { border-color: rgba(255,255,255,0.2); color: var(--text-primary); }
.btn-red { background: var(--crimson); color: #fff; }
.btn-red:hover { background: var(--crimson-soft); box-shadow: 0 4px 20px var(--crimson-glow); }
.section-divider { border: none; border-top: 1px solid var(--border); margin: 1.8rem 0; }
.summary-row { display: flex; justify-content: space-between; align-items: center; padding: .75rem 0; border-bottom: 1px solid var(--border); font-size: .88rem; }
.summary-row:last-child { border-bottom: none; }
.summary-key { color: var(--text-muted); font-size: .75rem; letter-spacing: .1em; text-transform: uppercase; }
.summary-val { color: var(--text-primary); font-weight: 500; }
.summary-total .summary-key { color: var(--text-primary); }
.summary-total .summary-val { font-family: 'Barlow Condensed', sans-serif; font-size: 1.6rem; color: var(--crimson-soft); }
</style>