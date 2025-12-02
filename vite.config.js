import fs from 'node:fs'
import path from 'node:path'
import { brotliCompressSync, constants as zlibConstants, gzipSync } from 'node:zlib'
import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';

const __dirname = path.dirname(fileURLToPath(import.meta.url))

const WEIGHT_KEYWORDS = new Map([
  ['thin', 100],
  ['extralight', 200],
  ['ultralight', 200],
  ['light', 300],
  ['regular', 400],
  ['book', 400],
  ['normal', 400],
  ['medium', 500],
  ['semibold', 600],
  ['demibold', 600],
  ['bold', 700],
  ['extrabold', 800],
  ['ultrabold', 800],
  ['black', 900],
  ['heavy', 900],
])

const FONT_FORMATS = new Map([
  ['.woff2', 'woff2'],
  ['.woff', 'woff'],
  ['.otf', 'opentype'],
  ['.ttf', 'truetype'],
  ['.eot', 'embedded-opentype'],
])

const FORMAT_PRIORITY = new Map([
  ['woff2', 0],
  ['woff', 1],
  ['opentype', 2],
  ['truetype', 3],
  ['embedded-opentype', 4],
])

const STYLE_KEYWORDS = ['italic', 'oblique']

const CSS_ENTRY_ALIASES = new Map([
  ['resources/css/app.css', 'resources/css/app.scss'],
  ['resources/css/editor.css', 'resources/css/editor.scss'],
])

const COMPRESSIBLE_ASSET_PATTERN = /\.(css|js|mjs|cjs|html|json|svg|xml|txt|woff2?|wasm)$/i

const minifySvgSource = (source) => {
  return source
    .replace(/<!--.*?-->/gs, '')
    .replace(/>\s+</g, '><')
    .replace(/\s{2,}/g, ' ')
    .replace(/\s*=\s*/g, '=')
    .replace(/\s*(\/>)/g, '$1')
    .trim()
}

const createSvgMinifierPlugin = () => ({
  name: 'svg-minifier',
  apply: 'build',
  generateBundle(_options, bundle) {
    for (const asset of Object.values(bundle)) {
      if (asset.type !== 'asset') {
        continue
      }

      if (typeof asset.source !== 'string') {
        continue
      }

      if (!asset.fileName?.endsWith('.svg')) {
        continue
      }

      const minified = minifySvgSource(asset.source)

      if (minified.length <= asset.source.length) {
        asset.source = minified
      }
    }
  },
})

const createAssetCompressionPlugin = () => ({
  name: 'static-asset-compression',
  apply: 'build',
  generateBundle(_options, bundle) {
    for (const [fileName, asset] of Object.entries(bundle)) {
      if (!COMPRESSIBLE_ASSET_PATTERN.test(fileName)) {
        continue
      }

      const rawSource = asset.type === 'asset' ? asset.source : asset.code

      if (typeof rawSource === 'undefined') {
        continue
      }

      const buffer = Buffer.isBuffer(rawSource)
        ? rawSource
        : typeof rawSource === 'string'
          ? Buffer.from(rawSource)
          : rawSource instanceof Uint8Array
            ? Buffer.from(rawSource)
            : null

      if (!buffer || buffer.length < 1024) {
        continue
      }

      try {
        const brotli = brotliCompressSync(buffer, {
          params: {
            [zlibConstants.BROTLI_PARAM_QUALITY]: 11,
          },
        })

        this.emitFile({
          type: 'asset',
          fileName: `${fileName}.br`,
          source: brotli,
        })
      } catch (error) {
        this.warn(`Failed to brotli compress ${fileName}: ${error instanceof Error ? error.message : error}`)
      }

      try {
        const gzip = gzipSync(buffer, {
          level: zlibConstants.Z_BEST_COMPRESSION,
        })

        this.emitFile({
          type: 'asset',
          fileName: `${fileName}.gz`,
          source: gzip,
        })
      } catch (error) {
        this.warn(`Failed to gzip compress ${fileName}: ${error instanceof Error ? error.message : error}`)
      }
    }
  },
})

const normalizeSpaces = (value) => value.replace(/\s+/g, ' ').trim()

const ensureDirectory = (directoryPath) => {
  fs.mkdirSync(directoryPath, { recursive: true })
}

const readFontFiles = (directory) => {
  if (!fs.existsSync(directory)) {
    return []
  }

  const entries = fs.readdirSync(directory, { withFileTypes: true })
  const files = []

  for (const entry of entries) {
    const fullPath = path.join(directory, entry.name)

    if (entry.isDirectory()) {
      files.push(...readFontFiles(fullPath))
      continue
    }

    const ext = path.extname(entry.name).toLowerCase()

    if (FONT_FORMATS.has(ext)) {
      files.push(fullPath)
    }
  }

  return files
}

const parseFontMeta = (filePath) => {
  const basename = path.basename(filePath, path.extname(filePath))
  const lower = basename.toLowerCase()

  let style = 'normal'
  for (const keyword of STYLE_KEYWORDS) {
    if (lower.includes(keyword)) {
      style = keyword
      break
    }
  }

  let weight = 400
  for (const [keyword, value] of WEIGHT_KEYWORDS.entries()) {
    if (lower.includes(keyword)) {
      weight = value
      break
    }
  }

  const numericMatch = lower.match(/(?:^|[-_])(100|200|300|400|500|600|700|800|900)(?:$|[-_])/)
  if (numericMatch) {
    weight = Number(numericMatch[1])
  }

  let family = basename

  for (const keyword of [...STYLE_KEYWORDS, ...WEIGHT_KEYWORDS.keys()]) {
    const regex = new RegExp(keyword, 'ig')
    family = family.replace(regex, '')
  }

  family = normalizeSpaces(family.replace(/[-_]+/g, ' '))

  if (!family) {
    family = normalizeSpaces(basename.replace(/[-_]+/g, ' '))
  }

  family = family.replace(/([a-z])([A-Z])/g, '$1 $2')
  family = normalizeSpaces(family)

  return { family, weight, style }
}

const buildFontFaceContent = (fonts, outputFile) => {
  if (fonts.length === 0) {
    return `// Auto-generated file. No fonts were discovered in resources/fonts.\n`
  }

  const groups = new Map()

  for (const fontPath of fonts) {
    const meta = parseFontMeta(fontPath)
    const ext = path.extname(fontPath).toLowerCase()
    const format = FONT_FORMATS.get(ext)

    if (!format) {
      continue
    }

    const key = `${meta.family}__${meta.weight}__${meta.style}`
    const relativePath = path.relative(path.dirname(outputFile), fontPath).split(path.sep).join('/')

    if (!groups.has(key)) {
      groups.set(key, { ...meta, sources: [] })
    }

    groups.get(key).sources.push({ path: relativePath, format })
  }

  const sortedGroups = Array.from(groups.values()).sort((a, b) => {
    if (a.family === b.family) {
      if (a.weight === b.weight) {
        return a.style.localeCompare(b.style)
      }

      return a.weight - b.weight
    }

    return a.family.localeCompare(b.family)
  })

  const lines = ['// Auto-generated file. Update fonts in resources/fonts instead.']

  for (const group of sortedGroups) {
    const sources = group.sources.sort((a, b) => {
      const priorityA = FORMAT_PRIORITY.get(a.format) ?? Number.MAX_SAFE_INTEGER
      const priorityB = FORMAT_PRIORITY.get(b.format) ?? Number.MAX_SAFE_INTEGER

      if (priorityA === priorityB) {
        return a.path.localeCompare(b.path)
      }

      return priorityA - priorityB
    })

    lines.push('', '@font-face {')
    lines.push(`  font-family: '${group.family}';`)
    lines.push(`  font-style: ${group.style};`)
    lines.push(`  font-weight: ${group.weight};`)

    sources.forEach((source, index) => {
      const prefix = index === 0 ? '  src: ' : '       '
      const suffix = index === sources.length - 1 ? ';' : ','
      lines.push(`${prefix}url('${source.path}') format('${source.format}')${suffix}`)
    })

    lines.push('  font-display: swap;')
    lines.push('}')
  }

  lines.push('')
  return `${lines.join('\n')}\n`
}

const writeFileIfChanged = (filePath, content) => {
  if (fs.existsSync(filePath)) {
    const existing = fs.readFileSync(filePath, 'utf8')
    if (existing === content) {
      return false
    }
  }

  ensureDirectory(path.dirname(filePath))
  fs.writeFileSync(filePath, content)
  return true
}

const fontFaceGeneratorPlugin = () => {
  const fontsDir = path.resolve(__dirname, 'resources/fonts')
  const outputFile = path.resolve(__dirname, 'resources/css/_fonts.scss')

  const generate = () => {
    const fonts = readFontFiles(fontsDir)
    const content = buildFontFaceContent(fonts, outputFile)
    return writeFileIfChanged(outputFile, content)
  }

  return {
    name: 'theme-font-face-generator',
    buildStart() {
      generate()
    },
    generateBundle() {
      generate()
    },
    configureServer(server) {
      ensureDirectory(fontsDir)
      const run = () => {
        if (generate()) {
          server.ws.send({ type: 'full-reload', path: '*' })
        }
      }

      server.watcher.add(fontsDir)

      const handle = (file) => {
        if (!file) {
          return
        }

        const resolved = path.resolve(file)
        if (!resolved.startsWith(fontsDir)) {
          return
        }

        if (!FONT_FORMATS.has(path.extname(resolved).toLowerCase())) {
          return
        }

        run()
      }

      const handleDir = (dir) => {
        if (!dir) {
          return
        }

        const resolved = path.resolve(dir)
        if (!resolved.startsWith(fontsDir)) {
          return
        }

        run()
      }

      server.watcher.on('add', handle)
      server.watcher.on('change', handle)
      server.watcher.on('unlink', handle)
      server.watcher.on('addDir', handleDir)
      server.watcher.on('unlinkDir', handleDir)

      run()
    },
  }
}

const createCssEntryAliasPlugin = () => {
  const aliasEntries = Array.from(CSS_ENTRY_ALIASES.entries())

  const rewriteUrl = (url) => {
    if (!url) {
      return null
    }

    const [rawPath, search = ''] = url.split('?')
    const normalizedPath = rawPath.replace(/^\/+/, '')

    for (const [alias, target] of aliasEntries) {
      if (normalizedPath === alias) {
        const rewritten = `/${target}${search ? `?${search}` : ''}`
        return rewritten
      }
    }

    return null
  }

  const manifestPath = path.resolve(__dirname, 'public/build/manifest.json')

  const updateManifest = () => {
    if (!fs.existsSync(manifestPath)) {
      return
    }

    const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'))
    let changed = false

    for (const [alias, target] of aliasEntries) {
      if (!manifest[target]) {
        continue
      }

      const targetEntry = JSON.parse(JSON.stringify(manifest[target]))

      if (!manifest[alias]) {
        manifest[alias] = targetEntry
        changed = true
        continue
      }

      const current = JSON.stringify(manifest[alias])
      const incoming = JSON.stringify(targetEntry)

      if (current !== incoming) {
        manifest[alias] = targetEntry
        changed = true
      }
    }

    if (changed) {
      ensureDirectory(path.dirname(manifestPath))
      fs.writeFileSync(manifestPath, `${JSON.stringify(manifest, null, 2)}\n`)
    }
  }

  return {
    name: 'css-entry-alias',
    configureServer(server) {
      server.middlewares.use((req, _res, next) => {
        const rewritten = rewriteUrl(req.url ?? '')

        if (rewritten) {
          req.url = rewritten
        }

        next()
      })
    },
    closeBundle() {
      updateManifest()
    },
    generateBundle() {
      updateManifest()
    },
  }
}

export default defineConfig({
  // Base path should match where WordPress serves the built assets
  base: '/wp-content/themes/dev/public/build/',
  plugins: [
    fontFaceGeneratorPlugin(),
    createCssEntryAliasPlugin(),
    tailwindcss(),
    laravel({
      input: [
        'resources/css/app.scss',
        'resources/js/app.js',
        'resources/css/editor.scss',
        'resources/js/editor.js',
        'resources/css/christmas.scss',
      ],
      refresh: true,
    }),

    wordpressPlugin(),

    // Generate the theme.json file in the public/build/assets directory
    // based on the Tailwind config and the theme.json file from base theme folder
    wordpressThemeJson({
      disableTailwindColors: false,
      disableTailwindFonts: false,
      disableTailwindFontSizes: false,
    }),
    createSvgMinifierPlugin(),
    createAssetCompressionPlugin(),
  ],
  resolve: {
    alias: {
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
      ...Object.fromEntries(
        Array.from(CSS_ENTRY_ALIASES.entries(), ([alias, target]) => [
          alias,
          path.resolve(__dirname, target),
        ]),
      ),
    },
  },
  css: {
    preprocessorOptions: {
      scss: {
        quietDeps: true,
        silenceDeprecations: ['import', 'global-builtin', 'function-units', 'color-functions', 'abs-percent'],
      },
    },
  },
  build: {
    outDir: 'public/build',
    assetsDir: 'assets',
    minify: 'esbuild',
    target: 'es2019',
  },
  esbuild: {
    minifyIdentifiers: true,
    minifySyntax: true,
    minifyWhitespace: true,
  },
})
