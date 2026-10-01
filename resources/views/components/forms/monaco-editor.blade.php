<div wire:key="{{ random_int(0, PHP_INT_MAX) }}" class="coolify-monaco-editor flex-1">
    <div x-ref="monacoRef" x-data="{
        monacoVersion: '0.52.2',
        monacoContent: @entangle($id),
        monacoLanguage: '',
        monacoLoader: true,
        monacoFontSize: '12.5px',
        monacoId: $id('monaco-editor'),
        monacoEditor(editor) {
            editor.onDidChangeModelContent((e) => {
                this.monacoContent = editor.getValue();
            });
        },
        monacoEditorAddLoaderScriptToHead() {
            // Use a global flag to prevent duplicate script loading
            if (!window.__coolifyMonacoLoaderAdding && typeof _amdLoaderGlobal === 'undefined') {
                window.__coolifyMonacoLoaderAdding = true;
                let script = document.createElement('script');
                script.src = `/js/monaco-editor-${this.monacoVersion}/min/vs/loader.js`;
                script.onload = () => {
                    window.__coolifyMonacoLoaderAdding = false;
                };
                script.onerror = () => {
                    window.__coolifyMonacoLoaderAdding = false;
                };
                document.head.appendChild(script);
            }
        }
    }" x-modelable="monacoContent">
        <div x-cloak x-init="if (typeof _amdLoaderGlobal == 'undefined' && !window.__coolifyMonacoLoaderAdding) {
            monacoEditorAddLoaderScriptToHead();
        }
        let monacoLoaderInterval = setInterval(() => {
            if (typeof _amdLoaderGlobal !== 'undefined') {
                require.config({ paths: { 'vs': `/js/monaco-editor-${monacoVersion}/min/vs` } });
                let proxy = URL.createObjectURL(new Blob([`self.MonacoEnvironment={baseUrl:'${window.location.origin}/js/monaco-editor-${monacoVersion}/min'};importScripts('${window.location.origin}/js/monaco-editor-${monacoVersion}/min/vs/base/worker/workerMain.js');`], { type: 'text/javascript' }));
                window.MonacoEnvironment = { getWorkerUrl: () => proxy };
                require(['vs/editor/editor.main'], () => {
                    if (!window.__coolifyMonacoThemeDefined) {
                        monaco.editor.defineTheme('coolify-dark', {
                            base: 'vs-dark',
                            inherit: true,
                            rules: [
                                { token: '', foreground: 'e8e8ea' },
                                { token: 'comment', foreground: '7d8590' },
                                { token: 'keyword', foreground: 'ff7b72' },
                                { token: 'string', foreground: 'a5d6ff' },
                                { token: 'number', foreground: '79c0ff' },
                                { token: 'constant', foreground: '79c0ff' },
                                { token: 'type', foreground: 'ffa657' },
                                { token: 'variable', foreground: 'ffa657' },
                                { token: 'function', foreground: 'd2a8ff' },
                                { token: 'key', foreground: '7ee787' },
                                { token: 'attribute.name', foreground: '7ee787' },
                                { token: 'string.key.json', foreground: '7ee787' },
                                { token: 'tag', foreground: '7ee787' },
                                { token: 'delimiter', foreground: '8e8e93' },
                                { token: 'operator', foreground: 'ff7b72' }
                            ],
                            colors: {
                                'editor.background': '#0a0a0a',
                                'editor.foreground': '#e8e8ea',
                                'editorGutter.background': '#0a0a0a',
                                'editorLineNumber.foreground': '#ffffff33',
                                'editorLineNumber.activeForeground': '#ffffff73',
                                'editorCursor.foreground': '#f5f5f7',
                                'editor.selectionBackground': '#ffffff26',
                                'editor.inactiveSelectionBackground': '#ffffff14',
                                'editorIndentGuide.background1': '#ffffff0d',
                                'editorWidget.background': '#1c1c1e',
                                'editorWidget.border': '#ffffff1a',
                                'editorStickyScroll.background': '#0a0a0a',
                                'minimap.background': '#0a0a0a',
                                'scrollbarSlider.background': '#ffffff1a',
                                'scrollbarSlider.hoverBackground': '#ffffff2e',
                                'scrollbarSlider.activeBackground': '#ffffff40',
                                'scrollbar.shadow': '#00000000'
                            }
                        });
                        window.__coolifyMonacoThemeDefined = true;
                    }
                    @if ($language === 'nginx')
                    if (!monaco.languages.getLanguages().some((registered) => registered.id === 'nginx')) {
                        monaco.languages.register({ id: 'nginx' });
                        monaco.languages.setLanguageConfiguration('nginx', {
                            comments: { lineComment: '#' },
                            brackets: [['{', '}']],
                            autoClosingPairs: [
                                { open: '{', close: '}' },
                            ],
                        });
                        monaco.languages.setMonarchTokensProvider('nginx', {
                            defaultToken: '',
                            tokenizer: {
                                root: [
                                    [/#.*$/, 'comment'],
                                    [/\$[A-Za-z_]\w*/, 'variable'],
                                    [/^\s*(server|location|upstream|http|events|types|map|if|include|listen|server_name|root|index|alias|return|rewrite|try_files|proxy_pass|proxy_set_header|proxy_http_version|fastcgi_pass|fastcgi_param|add_header|expires|error_page|access_log|error_log|gzip|gzip_types|charset|sendfile|keepalive_timeout|client_max_body_size|default_type|worker_processes|worker_connections|ssl_certificate|ssl_certificate_key|allow|deny|autoindex|set|break|internal|limit_req|limit_conn|resolver)\b/, 'keyword'],
                                    [/^\s*[A-Za-z_][\w.]*/, 'type'],
                                    [/\u0022([^\u0022\\]|\\.)*\u0022/, 'string'],
                                    [/'([^'\\]|\\.)*'/, 'string'],
                                    [/\b\d+(\.\d+)?(ms|[smhdwMy]|[kKmMgG])?\b/, 'number'],
                                    [/[{}();]/, 'delimiter'],
                                    [/(~\*?|\^~|=|\!=)/, 'operator'],
                                ],
                            },
                        });
                    }
                    @endif
                    const editor = monaco.editor.create($refs.monacoEditorElement, {
                        value: monacoContent,
                        theme: 'coolify-dark',
                        wordWrap: 'on',
                        readOnly: '{{ $readonly ?? false }}',
                        minimap: { enabled: false },
                        fontSize: monacoFontSize,
                        fontFamily: "'Geist Mono', ui-monospace, SFMono-Regular, Menlo, monospace",
                        lineHeight: 22,
                        lineNumbersMinChars: 3,
                        automaticLayout: true,
                        language: '{{ $language }}',
                        placeholder: 'Start typing here',
                        domReadOnly: '{{ $readonly ?? false }}',
                        contextmenu: '!{{ $readonly ?? false }}',
                        renderLineHighlight: 'none',
                        stickyScroll: { enabled: false },
                        padding: { top: 12, bottom: 12 },
                        overviewRulerLanes: 0,
                        overviewRulerBorder: false,
                        hideCursorInOverviewRuler: true,
                        scrollbar: {
                            vertical: 'auto',
                            horizontal: 'auto',
                            verticalScrollbarSize: 8,
                            horizontalScrollbarSize: 8,
                            useShadows: false
                        }
                    });
        
                    monacoEditor(editor);

                    document.getElementById(monacoId).editor = editor;

                    @if ($autofocus)
                    // Auto-focus the editor
                    setTimeout(() => editor.focus(), 100);
                    @endif
        
                    $watch('monacoContent', value => {
                        if (editor.getValue() !== value) {
                            editor.setValue(value);
                        }
                    });
        
        
                });
                clearInterval(monacoLoaderInterval);
                monacoLoader = false;
        
            }
        }, 5);" :id="monacoId">
        </div>
        <div class="relative z-10 w-full h-full">
            <div x-ref="monacoEditorElement" class="w-full text-md {{ $readonly ? 'opacity-65' : '' }}" style="height: var(--editor-height, calc(100vh - 20rem)); min-height: 150px;"></div>
        </div>
    </div>
</div>
