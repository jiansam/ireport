// 简单的 React 应用，不依赖外部包
const { createElement, useState } = React;
const { createRoot } = ReactDOM;

function App() {
    const [count, setCount] = useState(0);
    
    return createElement('div', {
        style: { 
            padding: '20px',
            fontFamily: 'Arial, sans-serif',
            textAlign: 'center',
            minHeight: '100vh',
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            color: 'white'
        }
    },
        createElement('h1', { style: { fontSize: '3rem', marginBottom: '2rem' } }, 
            'React + Tailwind CSS 4'),
        createElement('p', { style: { fontSize: '1.2rem', marginBottom: '2rem' } }, 
            '計數器: ' + count),
        createElement('button', {
            onClick: () => setCount(count + 1),
            style: {
                background: '#4f46e5',
                color: 'white',
                border: 'none',
                padding: '12px 24px',
                borderRadius: '8px',
                fontSize: '1rem',
                cursor: 'pointer',
                margin: '10px'
            }
        }, '增加'),
        createElement('button', {
            onClick: () => setCount(count - 1),
            style: {
                background: '#dc2626',
                color: 'white',
                border: 'none',
                padding: '12px 24px',
                borderRadius: '8px',
                fontSize: '1rem',
                cursor: 'pointer',
                margin: '10px'
            }
        }, '減少'),
        createElement('p', { style: { marginTop: '2rem', opacity: '0.8' } }, 
            'React + Tailwind CSS 4 測試！')
    );
}

const container = document.getElementById('app');
if (container) {
    const root = createRoot(container);
    root.render(createElement(App));
}
