<style>
    .gallery-photo-wrap {
        position: relative;
        overflow: hidden;
    }
    .gallery-photo-meta {
        position: absolute;
        inset-inline: 0;
        bottom: 0;
        padding: 1.75rem 0.65rem 0.55rem;
        background: linear-gradient(to top, rgba(17, 24, 39, 0.92) 0%, rgba(17, 24, 39, 0.55) 55%, transparent 100%);
        color: #fff;
        pointer-events: none;
    }
    .gallery-photo-meta--compact {
        padding: 1.25rem 0.5rem 0.45rem;
    }
    .gallery-photo-meta__league {
        margin: 0;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #b4f000;
        line-height: 1.25;
    }
    .gallery-photo-meta__division {
        margin: 0.15rem 0 0;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.3;
        color: rgba(255, 255, 255, 0.95);
    }
    .gallery-photo-meta__match {
        margin: 0.2rem 0 0;
        font-size: 10px;
        font-weight: 500;
        line-height: 1.35;
        color: rgba(255, 255, 255, 0.82);
    }
    .gallery-photo-meta__score {
        margin: 0.35rem 0 0;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.3;
        letter-spacing: 0.02em;
        color: #b4f000;
    }
    .gallery-photo-meta--below {
        position: static;
        padding: 0.55rem 0.65rem 0.6rem;
        background: #f9fafb;
        border-top: 1px solid #eeeeee;
    }
    .gallery-photo-meta--below .gallery-photo-meta__league {
        color: #2f7a2a;
    }
    .gallery-photo-meta--below .gallery-photo-meta__division {
        color: #374151;
    }
    .gallery-photo-meta--below .gallery-photo-meta__match {
        color: #6b7280;
    }
    .gallery-photo-meta--below .gallery-photo-meta__score {
        color: #2f7a2a;
    }
    .gallery-photo-meta--overlay {
        position: absolute;
        inset-inline: 0;
        bottom: 0;
        z-index: 2;
        padding: 2rem 0.65rem 0.6rem;
        background: linear-gradient(to top, rgba(17, 24, 39, 0.94) 0%, rgba(17, 24, 39, 0.5) 70%, transparent 100%);
    }
    .gallery-photo-meta--overlay .gallery-photo-meta__league {
        font-size: 11px;
    }
    .gallery-photo-meta--overlay .gallery-photo-meta__division {
        font-size: 12px;
    }
    .gallery-photo-meta--overlay .gallery-photo-meta__match {
        font-size: 11px;
    }
    .gallery-photo-meta--overlay .gallery-photo-meta__score {
        font-size: 13px;
    }
    .gallery-photo-meta--compact .gallery-photo-meta__score {
        font-size: 11px;
    }
</style>
