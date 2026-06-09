import React from "react";
import { useStoreState } from "easy-peasy";
import { ApplicationStore } from "@/state";
import styled from "styled-components/macro";
import tw from "twin.macro";
import Md2React from "@/reviactyl/ui/Md2React";
import SocialLinks from "@/reviactyl/ui/SocialLinks";

const Container = styled.div`
    ${tw`mt-8 mb-6 px-4`}
`;

const Copyright = styled.div`
    ${tw`text-center text-neutral-600 text-xs leading-relaxed`}

    a {
        ${tw`text-neutral-500 hover:text-neutral-400 transition-colors no-underline`}
    }
`;

export default () => {
    const copyright = useStoreState(
        (state: ApplicationStore) => state.settings.data?.arix?.copyright
    );

    return (
        <Container>
            <SocialLinks />
            <Copyright>
                <a rel={"noopener nofollow noreferrer"} href={"https://royal.dev"} target={"_blank"}>
                    Royal&trade;
                </a>
                &nbsp;&copy; {new Date().getFullYear()}
            </Copyright>
            {copyright && (
                <Copyright>
                    <Md2React markdown={copyright} />
                </Copyright>
            )}
        </Container>
    );
};
