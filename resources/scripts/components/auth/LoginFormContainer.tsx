import React, { forwardRef, useEffect, useRef, useState } from "react";
import { Form } from "formik";
import styled from "styled-components/macro";
import FlashMessageRender from "@/components/FlashMessageRender";
import tw from "twin.macro";
import Footer from "@/reviactyl/ui/Footer";
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';
import { SupportIcon } from '@heroicons/react/outline';
import { FaDiscord } from "react-icons/fa";

type Props = React.DetailedHTMLProps<React.FormHTMLAttributes<HTMLFormElement>, HTMLFormElement> & {
  title?: string;
};

const PageWrapper = styled.div`
  display: flex;
  min-height: 100vh;
  width: 100%;
  background: #050508;
  position: relative;
  overflow: hidden;

  @media (max-width: 900px) {
    flex-direction: column;
  }
`;

const Canvas = styled.canvas`
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
`;

const LeftPanel = styled.div`
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 4rem;
  position: relative;
  z-index: 2;

  @media (max-width: 900px) {
    padding: 2rem;
    min-height: auto;
  }
`;

const GlowBadge = styled.div`
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(168, 85, 247, 0.1);
  border: 0.5px solid rgba(168, 85, 247, 0.3);
  border-radius: 20px;
  padding: 6px 16px;
  margin-bottom: 1.5rem;
  width: fit-content;
  box-shadow: 0 0 20px rgba(168, 85, 247, 0.08);
`;

const GlowBadgeDot = styled.div`
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #a855f7;
  box-shadow: 0 0 8px #a855f7;
`;

const GlowBadgeText = styled.span`
  font-size: 11px;
  color: #a855f7;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  font-weight: 600;
`;

const HeroTitle = styled.h1`
  font-size: 48px;
  font-weight: 800;
  line-height: 1.05;
  color: #fff;
  text-align: center;
  letter-spacing: -0.03em;
  margin-bottom: 1rem;

  .gradient {
    background: linear-gradient(135deg, #a855f7, #ec4899);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .glow {
    color: #e2e8f0;
    text-shadow: 0 0 40px rgba(168, 85, 247, 0.3);
  }

  @media (max-width: 900px) {
    font-size: 36px;
  }
`;

const HeroTagline = styled.p`
  font-size: 15px;
  color: #94a3b8;
  text-align: center;
  line-height: 1.7;
  max-width: 380px;
  margin-bottom: 2rem;
  font-weight: 400;
`;

const StatsRow = styled.div`
  display: flex;
  gap: 2rem;
  align-items: center;
  justify-content: center;
  padding: 1.25rem 1.5rem;
  background: rgba(255, 255, 255, 0.03);
  border: 0.5px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
`;

const Stat = styled.div`
  display: flex;
  flex-direction: column;
  gap: 4px;
  align-items: center;
`;

const StatNum = styled.div`
  font-size: 22px;
  font-weight: 800;
  color: #f1f5f9;
  letter-spacing: -0.02em;
`;

const StatLabel = styled.div`
  font-size: 10px;
  color: #64748b;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  font-weight: 600;
`;

const StatDivider = styled.div`
  width: 0.5px;
  height: 36px;
  background: rgba(255, 255, 255, 0.08);
`;

const RightPanel = styled.div`
  width: 420px;
  min-height: 100vh;
  background: rgb(10 10 20 / 0.97);
  border-left: 0.5px solid rgb(255 255 255 / 0.06);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  position: relative;
  z-index: 2;

  @media (max-width: 900px) {
    width: 100%;
    min-height: auto;
    border-left: none;
    border-top: 0.5px solid rgb(255 255 255 / 0.06);
    padding: 2rem 1.5rem;
  }
`;

const Card = styled.div`
  width: 100%;
  max-width: 380px;
  display: flex;
  flex-direction: column;
  position: relative;
`;

const TopBar = styled.div`
  height: 3px;
  background: linear-gradient(90deg, rgb(var(--color-primary)), rgb(var(--color-secondary)));
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
`;

const FormEyebrow = styled.div`
  font-size: 11px;
  color: rgb(var(--color-primary));
  letter-spacing: 0.15em;
  text-transform: uppercase;
  font-weight: 500;
  margin-bottom: 0.5rem;
`;

const FormTitle = styled.div`
  font-size: 24px;
  font-weight: 700;
  color: #f3f4f6;
  margin-bottom: 0.3rem;
`;

const FormSub = styled.div`
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 2.25rem;
`;

export default forwardRef<HTMLFormElement, Props>(({ title, ...props }, ref) => {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [guildData, setGuildData] = useState<{ instant_invite: string } | null>(null);

  const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);
  const logo = useStoreState((state: ApplicationStore) => state.settings.data!.royal.logo);
  const logoLight = useStoreState((state: ApplicationStore) => state.settings.data!.royal.logoLight);
  const logoHeight = useStoreState((state: ApplicationStore) => state.settings.data!.royal.logoHeight);
  const fullLogo = useStoreState((state: ApplicationStore) => state.settings.data!.royal.fullLogo);
  const loginBackground = useStoreState((state: ApplicationStore) => state.settings.data!.royal.loginBackground);
  const loginLayout = useStoreState((state: ApplicationStore) => state.settings.data!.royal.loginLayout);
  const loginGradient = useStoreState((state: ApplicationStore) => state.settings.data!.royal.loginGradient);
  const logoPosition = useStoreState((state: ApplicationStore) => state.settings.data!.royal.logoPosition);
  const socialPosition = useStoreState((state: ApplicationStore) => state.settings.data!.royal.socialPosition);
  const discord = useStoreState((state: ApplicationStore) => state.settings.data!.royal.discord);
  const support = useStoreState((state: ApplicationStore) => state.settings.data!.royal.support);
  const heroBadge = useStoreState((state: ApplicationStore) => state.settings.data!.royal.heroBadge);
  const heroTitle = useStoreState((state: ApplicationStore) => state.settings.data!.royal.heroTitle);
  const heroTagline = useStoreState((state: ApplicationStore) => state.settings.data!.royal.heroTagline);

  const darkMode = localStorage.getItem('darkMode') === 'true';

  useEffect(() => {
    if (!discord) return;
    const fetchData = async () => {
      try {
        const response = await fetch(`https://discord.com/api/guilds/${discord}/widget.json`);
        if (!response.ok) throw new Error('Failed to fetch guild data');
        const data = await response.json();
        setGuildData(data);
      } catch (error) {
        console.error('Error fetching guild data:', error);
      }
    };
    fetchData();
  }, [discord]);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    const resize = () => {
      canvas.width = canvas.offsetWidth;
      canvas.height = canvas.offsetHeight;
    };
    resize();
    window.addEventListener("resize", resize);

    const pts = Array.from({ length: 50 }, () => ({
      x: Math.random() * canvas.width * 0.65,
      y: Math.random() * canvas.height,
      vx: (Math.random() - 0.5) * 0.2,
      vy: (Math.random() - 0.5) * 0.2,
    }));

    const stars = Array.from({ length: 80 }, () => ({
      x: Math.random() * canvas.width * 0.65,
      y: Math.random() * canvas.height,
      r: Math.random() * 0.9 + 0.2,
      a: Math.random() * 0.45 + 0.1,
      p: Math.random() * Math.PI * 2,
    }));

    let t = 0;
    let animId: number;

    const draw = () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      t += 0.008;

      stars.forEach((s) => {
        const a = s.a * (0.5 + 0.5 * Math.sin(t + s.p));
        ctx.beginPath();
        ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(180,170,255,${a})`;
        ctx.fill();
      });

      pts.forEach((p) => {
        p.x += p.vx;
        p.y += p.vy;
        if (p.x < 0 || p.x > canvas.width * 0.65) p.vx *= -1;
        if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
      });

      for (let i = 0; i < pts.length; i++) {
        for (let j = i + 1; j < pts.length; j++) {
          const dx = pts[i].x - pts[j].x;
          const dy = pts[i].y - pts[j].y;
          const d = Math.sqrt(dx * dx + dy * dy);
          if (d < 100) {
            ctx.beginPath();
            ctx.moveTo(pts[i].x, pts[i].y);
            ctx.lineTo(pts[j].x, pts[j].y);
            ctx.strokeStyle = `rgba(124,110,245,${0.2 * (1 - d / 100)})`;
            ctx.lineWidth = 0.5;
            ctx.stroke();
          }
        }
      }

      pts.forEach((p) => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, 1, 0, Math.PI * 2);
        ctx.fillStyle = "rgba(124,110,245,0.5)";
        ctx.fill();
      });

      animId = requestAnimationFrame(draw);
    };

    draw();
    return () => {
      window.removeEventListener("resize", resize);
      cancelAnimationFrame(animId);
    };
  }, []);

  const showBgImage = loginLayout == 1 || loginLayout == 4;

  return (
    <PageWrapper css={showBgImage && loginBackground ? `background-image:url(${loginBackground});background-size:cover;background-position:center;` : ''}>
      {String(loginGradient) === 'true' && loginLayout != 2 && loginLayout != 3 &&
        <div css={'position:absolute;inset:0;z-index:1;background-image:radial-gradient(circle, color-mix(in srgb, var(--gray800) 45%, transparent) 0%, var(--gray800) 100%);'} />
      }

      {logoPosition == 2 &&
        <div css={'position:fixed;top:0;left:0;right:0;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:16px 20px'}>
          <div css={'display:flex;gap:8px;align-items:center;font-weight:600;font-size:18px;color:#f9fafb'}>
            <img src={darkMode ? logoLight : logo} alt={name + 'logo'} css={`height:${logoHeight};`} />
            {String(fullLogo) === 'false' && name}
          </div>
          {socialPosition == 1 &&
            <div css={'display:flex;gap:16px'}>
              {discord && guildData && <a css={'display:flex;gap:4px;align-items:center;color:#9ca3af;text-decoration:none;transition:color 0.3s'} href={guildData.instant_invite}><FaDiscord /> Discord</a>}
              {support && <a css={'display:flex;gap:4px;align-items:center;color:#9ca3af;text-decoration:none;transition:color 0.3s'} href={support}><SupportIcon css={'width:20px'} /> Support</a>}
            </div>
          }
        </div>
      }

      <Canvas ref={canvasRef} />

      <LeftPanel>
        <GlowBadge>
          <GlowBadgeDot />
          <GlowBadgeText>{heroBadge || 'Neon Gaming Network'}</GlowBadgeText>
        </GlowBadge>
        <HeroTitle>
          {heroTitle ? (
            heroTitle.split('\\n').map((line, i) => (
              <span key={i}>{i % 2 === 1 ? <span className="gradient">{line}</span> : <span className="glow">{line}</span>}<br /></span>
            ))
          ) : (
            <>
              <span className="glow">Power Your</span><br />
              <span className="gradient">Game. Instantly.</span>
            </>
          )}
        </HeroTitle>
        <HeroTagline>{heroTagline || 'Blazing-fast servers with one-click deploy, real-time monitoring, and zero lag — built for competitive gaming.'}</HeroTagline>
        <StatsRow>
          <Stat>
            <StatNum>99.99%</StatNum>
            <StatLabel>↑ Uptime</StatLabel>
          </Stat>
          <StatDivider />
          <Stat>
            <StatNum>&lt;1ms</StatNum>
            <StatLabel>⏱ Latency</StatLabel>
          </Stat>
          <StatDivider />
          <Stat>
            <StatNum>24/7</StatNum>
            <StatLabel>⚡ Support</StatLabel>
          </Stat>
          <StatDivider />
          <Stat>
            <StatNum>10K+</StatNum>
            <StatLabel>🛡 Servers</StatLabel>
          </Stat>
        </StatsRow>
      </LeftPanel>

      <RightPanel css={(loginLayout == 2 || loginLayout == 3) && loginBackground ? `background-image:url(${loginBackground});background-size:cover;background-position:center;` : ''}>
        {(loginLayout == 2 || loginLayout == 3) && String(loginGradient) === 'true' &&
          <div css={'position:absolute;inset:0;z-index:1;background-image:radial-gradient(circle, color-mix(in srgb, var(--gray800) 45%, transparent) 0%, var(--gray800) 100%)'} />
        }
        <Card css={'position:relative;z-index:2'}>
          <TopBar />
          {logoPosition == 1 &&
            <div css={'display:flex;gap:8px;align-items:center;font-weight:600;font-size:16px;color:#f9fafb;margin-bottom:16px;align-self:center'}>
              <img src={darkMode ? logoLight : logo} alt={name + 'logo'} css={`height:${logoHeight};`} />
              {String(fullLogo) === 'false' && name}
            </div>
          }
          {socialPosition == 1 && logoPosition != 2 &&
            <div css={'display:flex;gap:16px;margin-bottom:16px;justify-content:center'}>
              {discord && guildData && <a css={'display:flex;gap:4px;align-items:center;color:#9ca3af;text-decoration:none;transition:color 0.3s'} href={guildData.instant_invite}><FaDiscord /> Discord</a>}
              {support && <a css={'display:flex;gap:4px;align-items:center;color:#9ca3af;text-decoration:none;transition:color 0.3s'} href={support}><SupportIcon css={'width:20px'} /> Support</a>}
            </div>
          }
          <FlashMessageRender css={tw`mb-4`} />
          <FormEyebrow>Welcome back</FormEyebrow>
          <FormTitle>Sign in</FormTitle>
          <FormSub>Access your control panel</FormSub>
          <Form {...props} ref={ref}>
            {props.children}
          </Form>
          {socialPosition == 2 &&
            <div css={'display:flex;gap:16px;margin-top:16px;justify-content:center'}>
              {discord && guildData && <a css={'display:flex;gap:4px;align-items:center;color:#9ca3af;text-decoration:none;transition:color 0.3s'} href={guildData.instant_invite}><FaDiscord /> Discord</a>}
              {support && <a css={'display:flex;gap:4px;align-items:center;color:#9ca3af;text-decoration:none;transition:color 0.3s'} href={support}><SupportIcon css={'width:20px'} /> Support</a>}
            </div>
          }
          <Footer />
        </Card>
      </RightPanel>
    </PageWrapper>
  );
});
