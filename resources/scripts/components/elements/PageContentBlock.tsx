import React, { useEffect } from 'react';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';
import ContentContainer from '@/components/elements/ContentContainer';
import { CSSTransition } from 'react-transition-group';
import tw from 'twin.macro';
import Markdown from 'markdown-to-jsx';
import FlashMessageRender from '@/components/FlashMessageRender';

export interface PageContentBlockProps {
    title?: string;
    className?: string;
    showFlashKey?: string;
}

const PageContentBlock: React.FC<PageContentBlockProps> = ({ title, showFlashKey, className, children }) => {
    const copyright = useStoreState((state: ApplicationStore) => state.settings.data!.royal.copyright);
    
    useEffect(() => {
        if (title) {
            document.title = title;
        }
    }, [title]);

    return (
        <CSSTransition timeout={150} classNames={'fade'} appear in>
            <div className={'px-4'}>
                <ContentContainer css={tw`my-4 sm:mb-10 sm:mt-6`} className={className}>
                    {showFlashKey && <FlashMessageRender byKey={showFlashKey} css={tw`mb-4`} />}
                    {children}
                </ContentContainer>
                <ContentContainer css={tw`mb-4`}>
                    <p css={tw`text-center text-neutral-300 text-xs`}>
                        Royal Panel &copy; 2015 - {new Date().getFullYear()}
                    </p>
                    <p css={tw`text-center text-neutral-300 text-xs`}>
                        Designed by <a
                            rel={'noopener nofollow noreferrer'}
                            href={'https://github.com/royaldevlopments'}
                            target={'_blank'}
                            css={tw`no-underline text-neutral-300 hover:text-neutral-100 font-semibold`}
                        >Royal Devlopments</a>
                    </p>
                </ContentContainer>
            </div>
        </CSSTransition>
    );
};

export default PageContentBlock;
