/**
 * InkManager — Project card for Next.js portfolio
 *
 * Copy-paste ready. Requires:
 *   - Tailwind CSS configured
 *   - next/image (Next 13+ app router compatible)
 *   - next/link
 *
 * Drop the hero image at /public/projects/inkmanager-hero.gif (or .png)
 * Adjust the import path or use a regular <img> if you prefer.
 *
 * Usage:
 *   import { InkManagerCard } from './ProjectCard';
 *   <InkManagerCard />
 */

import Image from 'next/image';
import Link from 'next/link';

// ─────────────────────────────────────────────────────────────────────────────
// Reusable ProjectCard component — use this for OTHER projects too
// ─────────────────────────────────────────────────────────────────────────────
type Tech = { name: string; color: string };

type ProjectCardProps = {
  title: string;
  tagline: string;
  description: string;
  heroSrc: string;            // path to image/gif under /public
  heroAlt: string;
  tech: Tech[];
  liveUrl?: string;
  githubUrl: string;
  highlights?: string[];      // optional bullet list
};

export function ProjectCard({
  title,
  tagline,
  description,
  heroSrc,
  heroAlt,
  tech,
  liveUrl,
  githubUrl,
  highlights = [],
}: ProjectCardProps) {
  return (
    <article className="group relative overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-950 transition-all hover:border-red-500/40 hover:shadow-2xl hover:shadow-red-900/20">
      {/* Decorative gradient orb on hover */}
      <div className="pointer-events-none absolute -top-24 -right-24 h-64 w-64 rounded-full bg-red-600/10 blur-3xl opacity-0 transition-opacity duration-500 group-hover:opacity-100" />

      {/* Hero image / GIF */}
      <div className="relative aspect-video overflow-hidden border-b border-neutral-800 bg-neutral-900">
        <Image
          src={heroSrc}
          alt={heroAlt}
          fill
          className="object-cover transition-transform duration-700 group-hover:scale-105"
          sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 600px"
          unoptimized={heroSrc.endsWith('.gif')}
        />
        {/* Gradient overlay */}
        <div className="absolute inset-0 bg-gradient-to-t from-neutral-950 via-transparent to-transparent" />
      </div>

      <div className="relative p-6 sm:p-7">
        {/* Title + tagline */}
        <div className="mb-3">
          <h3 className="text-2xl font-bold tracking-tight text-white">
            {title}
          </h3>
          <p className="mt-1 text-sm text-red-400">{tagline}</p>
        </div>

        {/* Description */}
        <p className="text-sm leading-relaxed text-neutral-400">{description}</p>

        {/* Highlights */}
        {highlights.length > 0 && (
          <ul className="mt-4 space-y-1.5">
            {highlights.map((h, i) => (
              <li key={i} className="flex items-start gap-2 text-xs text-neutral-500">
                <span className="mt-1.5 h-1 w-1 flex-shrink-0 rounded-full bg-red-500" />
                <span>{h}</span>
              </li>
            ))}
          </ul>
        )}

        {/* Tech stack badges */}
        <div className="mt-5 flex flex-wrap gap-1.5">
          {tech.map((t) => (
            <span
              key={t.name}
              className={`rounded-md border px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider ${t.color}`}
            >
              {t.name}
            </span>
          ))}
        </div>

        {/* CTAs */}
        <div className="mt-6 flex gap-2">
          {liveUrl && (
            <Link
              href={liveUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-gradient-to-r from-red-600 to-red-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white shadow-lg shadow-red-900/30 transition-all hover:from-red-500 hover:to-red-600 hover:shadow-red-800/50 active:scale-95"
            >
              <svg className="h-3.5 w-3.5" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
              Live demo
            </Link>
          )}
          <Link
            href={githubUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center justify-center gap-1.5 rounded-lg border border-neutral-700 bg-neutral-900 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-neutral-300 transition-all hover:border-neutral-600 hover:bg-neutral-800 active:scale-95"
          >
            <svg className="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z" />
            </svg>
            GitHub
          </Link>
        </div>
      </div>
    </article>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// Pre-configured: InkManager card
// ─────────────────────────────────────────────────────────────────────────────
export function InkManagerCard() {
  return (
    <ProjectCard
      title="InkManager"
      tagline="Digital Studio System"
      description="Sistema de gestión para estudios de tatuaje con un body map 3D interactivo donde marcás cada tatuaje en su posición exacta sobre un modelo humano rotable."
      heroSrc="/projects/inkmanager-hero.gif"
      heroAlt="InkManager body map 3D rotando con markers de tatuajes"
      tech={[
        { name: 'PHP 8.2',    color: 'border-purple-500/30 bg-purple-500/10 text-purple-300' },
        { name: 'MySQL',      color: 'border-blue-500/30   bg-blue-500/10   text-blue-300'   },
        { name: 'Three.js',   color: 'border-neutral-500/30 bg-neutral-500/10 text-neutral-200' },
        { name: 'Tailwind',   color: 'border-cyan-500/30   bg-cyan-500/10   text-cyan-300'   },
        { name: 'Railway',    color: 'border-pink-500/30   bg-pink-500/10   text-pink-300'   },
      ]}
      liveUrl="https://inkmanager.up.railway.app"
      githubUrl="https://github.com/AlejoMFernandez/InkManager"
      highlights={[
        'Body map 3D con raycaster click-to-mark y markers persistentes',
        'CRM de clientes + calendario drag & drop con FullCalendar',
        'PHP vanilla MVC propio — sin framework, sin Composer, sin build step',
        'Identidad visual completa: splash intro, tipografía editorial, monograma SVG animado',
      ]}
    />
  );
}

export default InkManagerCard;
