import React from "react";

interface Md2ReactProps {
    markdown: string;
}

const parseBold = (text: string): (string | JSX.Element)[] => {
    const boldRegex = /\*\*(.*?)\*\*/g;
    const result: (string | JSX.Element)[] = [];
    let lastIndex = 0;
    let match;

    while ((match = boldRegex.exec(text)) !== null) {
        if (match.index > lastIndex) {
            result.push(text.slice(lastIndex, match.index));
        }
        result.push(<strong key={match.index}>{match[1]}</strong>);
        lastIndex = boldRegex.lastIndex;
    }

    if (lastIndex < text.length) {
        result.push(text.slice(lastIndex));
    }

    return result;
};

const Md2React: React.FC<Md2ReactProps> = ({ markdown }) => {
    const linkRegex = /\[([^\]]+)\]\(([^)]+)\)/g;
    const parts: (string | JSX.Element)[] = [];
    let lastIndex = 0;
    let match;

    while ((match = linkRegex.exec(markdown)) !== null) {
        if (match.index > lastIndex) {
            parts.push(...parseBold(markdown.slice(lastIndex, match.index)));
        }
        parts.push(
            <a key={match.index} href={match[2]} target="_blank" rel="noopener noreferrer">
                {match[1]}
            </a>
        );
        lastIndex = linkRegex.lastIndex;
    }

    if (lastIndex < markdown.length) {
        parts.push(...parseBold(markdown.slice(lastIndex)));
    }

    return <>{parts}</>;
};

export default Md2React;
