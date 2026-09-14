function attachMarkdownToolbar(textarea, opts = {}) {
  const toolbar = document.createElement('div');
  toolbar.className = 'md-toolbar';

  function makeButton(label, title, className, onClick) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = label;
    btn.title = title;
    btn.className = `md-toolbar-btn ${className || ''}`.trim();
    btn.addEventListener('click', onClick);
    return btn;
  }

  function wrapSelection(before, after) {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const value = textarea.value;
    const selected = value.slice(start, end) || 'text';
    textarea.value = value.slice(0, start) + before + selected + after + value.slice(end);
    textarea.focus();
    textarea.selectionStart = start + before.length;
    textarea.selectionEnd = start + before.length + selected.length;
    textarea.dispatchEvent(new Event('input'));
  }

  toolbar.appendChild(makeButton('B', 'Bold', 'is-bold', () => wrapSelection('**', '**')));
  toolbar.appendChild(makeButton('I', 'Italic', 'is-italic', () => wrapSelection('*', '*')));
  toolbar.appendChild(makeButton('S', 'Strikethrough', 'is-strike', () => wrapSelection('~~', '~~')));
  toolbar.appendChild(makeButton('Link', 'Insert a link', '', () => {
    const url = window.prompt('Paste the URL:');
    if (!url) return;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const value = textarea.value;
    const selected = value.slice(start, end);
    const insertion = selected ? `${selected} (${url})` : url;
    textarea.value = value.slice(0, start) + insertion + value.slice(end);
    textarea.focus();
    textarea.dispatchEvent(new Event('input'));
  }));

  if (opts.hint !== false) {
    const hint = document.createElement('span');
    hint.className = 'md-toolbar-hint';
    hint.textContent = opts.hint || 'Leave a blank line between paragraphs. Links are clickable automatically.';
    toolbar.appendChild(hint);
  }

  textarea.parentNode.insertBefore(toolbar, textarea);
}
