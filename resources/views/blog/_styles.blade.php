<style>
.blog-root {
	--bl-bg: #f5f7fb;
	--bl-card: #fff;
	--bl-ink: #0f172a;
	--bl-ink-muted: rgba(15,23,42,.65);
	--bl-border: rgba(15,23,42,.10);
	--bl-accent: #ff7a18;
	--bl-accent-hover: #e86a0f;
	background: var(--bl-bg);
	color: var(--bl-ink);
	font-family: 'Inter', system-ui, -apple-system, sans-serif;
	min-height: 60vh;
}
.blog-container { max-width: 1120px; margin: 0 auto; padding: 0 24px; }
.blog-hero { padding: 56px 24px 24px; text-align: center; max-width: 900px; margin: 0 auto; }
.blog-hero h1 { font-size: clamp(1.9rem, 3vw, 2.5rem); font-weight: 900; letter-spacing: -0.02em; margin: 0 0 10px; }
.blog-hero p { font-size: 1.0625rem; color: var(--bl-ink-muted); margin: 0 auto; max-width: 620px; }
.blog-section { padding: 32px 24px; max-width: 1120px; margin: 0 auto; }
.blog-section-title { display: flex; justify-content: space-between; align-items: end; margin-bottom: 16px; }
.blog-section-title h2 { margin: 0; font-size: 1.25rem; font-weight: 800; letter-spacing: -0.01em; }

.blog-crumbs { font-size: 13px; color: var(--bl-ink-muted); margin-bottom: 14px; }
.blog-crumbs a { color: var(--bl-ink-muted); text-decoration: none; }
.blog-crumbs a:hover { color: var(--bl-ink); }
.blog-crumbs span { margin: 0 6px; }

.blog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.blog-grid-lg { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
@media (max-width: 920px) { .blog-grid { grid-template-columns: repeat(2, 1fr); } .blog-grid-lg { grid-template-columns: 1fr; } }
@media (max-width: 620px) { .blog-grid { grid-template-columns: 1fr; } }

.blog-card {
	background: var(--bl-card);
	border: 1px solid var(--bl-border);
	border-radius: 14px;
	padding: 18px 20px;
	transition: transform .12s ease, box-shadow .12s ease;
	display: flex;
	flex-direction: column;
	gap: 10px;
	color: inherit;
	text-decoration: none;
}
.blog-card:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(15,23,42,.08); color: inherit; }
.blog-card-cat { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--bl-accent); }
.blog-card-title { font-size: 1.0625rem; font-weight: 800; line-height: 1.25; letter-spacing: -0.01em; margin: 0; }
.blog-card-excerpt { font-size: 13px; color: var(--bl-ink-muted); line-height: 1.55; margin: 0; }
.blog-card-meta { font-size: 11px; color: var(--bl-ink-muted); display: flex; gap: 10px; margin-top: auto; padding-top: 8px; }
.blog-card-pillar { border-color: var(--bl-accent); background: linear-gradient(135deg, #fff 0%, rgba(255,122,24,.04) 100%); }
.blog-card-pillar .blog-card-cat::before { content: '★ '; }

.blog-featured-card {
	grid-column: 1 / -1;
	background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
	color: #fff;
	padding: 32px;
	border-radius: 18px;
	display: flex;
	flex-direction: column;
	gap: 12px;
	text-decoration: none;
}
.blog-featured-card:hover { color: #fff; }
.blog-featured-card h3 { margin: 0; font-size: 1.5rem; font-weight: 900; letter-spacing: -0.015em; }
.blog-featured-card p { color: rgba(255,255,255,.78); margin: 0; font-size: 15px; }
.blog-featured-card .blog-card-cat { color: #ff7a18; }

.blog-post-body {
	max-width: 720px;
	margin: 0 auto;
	font-size: 17px;
	line-height: 1.7;
	color: #1f2937;
}
.blog-post-body h1 { font-size: 2rem; font-weight: 900; letter-spacing: -0.02em; margin: 0 0 16px; line-height: 1.15; }
.blog-post-body h2 { font-size: 1.375rem; font-weight: 800; letter-spacing: -0.01em; margin: 36px 0 12px; }
.blog-post-body h3 { font-size: 1.125rem; font-weight: 800; margin: 24px 0 10px; }
.blog-post-body p { margin: 0 0 16px; }
.blog-post-body ul, .blog-post-body ol { margin: 0 0 16px 24px; padding: 0; }
.blog-post-body li { margin-bottom: 6px; }
.blog-post-body a { color: var(--bl-accent); text-decoration: underline; text-underline-offset: 3px; }
.blog-post-body a:hover { color: var(--bl-accent-hover); }
.blog-post-body blockquote { border-left: 3px solid var(--bl-accent); padding-left: 16px; margin: 20px 0; font-style: italic; color: var(--bl-ink-muted); }
.blog-post-body code { background: rgba(15,23,42,.06); padding: 1px 6px; border-radius: 4px; font-size: 14px; }
.blog-post-body strong { font-weight: 700; color: var(--bl-ink); }

.blog-meta-row { font-size: 13px; color: var(--bl-ink-muted); display: flex; gap: 12px; margin: 20px 0; flex-wrap: wrap; align-items: center; }
.blog-meta-row .blog-card-cat { font-size: 11px; background: rgba(255,122,24,.12); padding: 3px 10px; border-radius: 999px; }

.blog-tag-pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; background: rgba(15,23,42,.06); color: var(--bl-ink); font-size: 12px; font-weight: 600; text-decoration: none; margin-right: 6px; }
.blog-tag-pill:hover { background: rgba(255,122,24,.14); color: var(--bl-accent); }

.blog-cta-inline {
	background: linear-gradient(135deg, rgba(255,122,24,.08), rgba(255,122,24,.02));
	border: 1px solid rgba(255,122,24,.22);
	border-radius: 14px;
	padding: 20px 24px;
	margin: 28px 0;
}
.blog-cta-inline h4 { margin: 0 0 6px; font-size: 1rem; font-weight: 800; }
.blog-cta-inline p { margin: 0 0 12px; font-size: 14px; color: var(--bl-ink-muted); }
.blog-cta-inline a { background: var(--bl-accent); color: #fff; padding: 9px 18px; border-radius: 10px; font-weight: 700; text-decoration: none; font-size: 14px; }
.blog-cta-inline a:hover { background: var(--bl-accent-hover); color: #fff; }

.blog-related { max-width: 900px; margin: 48px auto 0; padding: 0 24px; }

.blog-category-list { display: flex; flex-wrap: wrap; gap: 8px; }
.blog-category-pill { padding: 6px 14px; border-radius: 999px; background: var(--bl-card); border: 1px solid var(--bl-border); color: var(--bl-ink); font-size: 13px; font-weight: 600; text-decoration: none; }
.blog-category-pill:hover { border-color: var(--bl-accent); color: var(--bl-accent); }
.blog-category-pill-active { background: var(--bl-ink); color: #fff; border-color: var(--bl-ink); }
.blog-category-pill-active:hover { background: var(--bl-ink); color: #fff; }

.blog-pager { margin: 28px 0; display: flex; justify-content: center; }
</style>
