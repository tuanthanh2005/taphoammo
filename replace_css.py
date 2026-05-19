import os

filepath = r"d:\MMO\taphoammo\app\Views\home\index.php"
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

target = """    .cat-pill {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
        width: 100%;
    }

    .cat-pill:hover {
        transform: translateY(-4px);
    }

    .cat-pill-icon {
        width: 54px;
        height: 54px;
        background: var(--vip-gradient);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(139, 92, 246, 0.35);
        transition: all 0.3s ease;
        margin: 0 auto;
    }

    .cat-pill:hover .cat-pill-icon {
        transform: scale(1.1) rotate(8deg);
        box-shadow: 0 8px 24px rgba(139, 92, 246, 0.5);
    }

    .cat-pill-icon i {
        font-size: 1.25rem;
        color: white;
    }

    .cat-pill-name {
        font-size: 0.7rem;
        font-weight: 600;
        text-align: center;
        color: #374151;
        line-height: 1.2;
        white-space: normal;
        word-break: break-word;
        max-width: 100%;
    }"""

replacement = """    .cat-pill {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        width: 100%;
        padding: 16px 10px;
        border-radius: 20px;
        background: #ffffff;
        border: 1px solid rgba(139, 92, 246, 0.08);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }

    .cat-pill:hover {
        transform: translateY(-6px);
        background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
        border-color: rgba(139, 92, 246, 0.3);
        box-shadow: 0 12px 25px rgba(139, 92, 246, 0.15);
    }

    .cat-pill-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.5), 0 4px 10px rgba(139, 92, 246, 0.1);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        margin: 0 auto;
    }

    .cat-pill:hover .cat-pill-icon {
        transform: scale(1.08) rotate(5deg);
        background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
    }

    .cat-pill-icon i {
        font-size: 1.3rem;
        color: #8b5cf6;
        transition: all 0.3s ease;
    }

    .cat-pill:hover .cat-pill-icon i {
        color: white;
    }

    .cat-pill-name {
        font-size: 0.8rem;
        font-weight: 700;
        text-align: center;
        color: #1f2937;
        line-height: 1.3;
        white-space: normal;
        word-break: break-word;
        max-width: 100%;
        transition: color 0.3s ease;
    }

    .cat-pill:hover .cat-pill-name {
        color: #7c3aed;
    }"""

content_normalized = content.replace('\r\n', '\n')
target_normalized = target.replace('\r\n', '\n')
replacement_normalized = replacement.replace('\r\n', '\n')

new_content = content_normalized.replace(target_normalized, replacement_normalized)
if new_content == content_normalized:
    print("No replacement made. Target not found.")
else:
    if '\r\n' in content:
        new_content = new_content.replace('\n', '\r\n')
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Replacement successful.")
