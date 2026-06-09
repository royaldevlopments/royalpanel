import React, { forwardRef, useEffect, useRef } from "react";
import { Form } from "formik";
import styled from "styled-components/macro";
import FlashMessageRender from "@/components/FlashMessageRender";
import tw from "twin.macro";
import Footer from "@/reviactyl/ui/Footer";

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
  padding: 4rem;
  position: relative;
  z-index: 2;

  @media (max-width: 900px) {
    padding: 2rem;
    min-height: auto;
  }
`;

const Badge = styled.div`
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgb(var(--color-primary) / 0.1);
  border: 0.5px solid rgb(var(--color-primary) / 0.25);
  border-radius: 20px;
  padding: 5px 14px;
  margin-bottom: 1.5rem;
  width: fit-content;
`;

const BadgeDot = styled.div`
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: rgb(var(--color-primary));
`;

const BadgeText = styled.span`
  font-size: 11px;
  color: rgb(var(--color-primary) / 0.8);
  letter-spacing: 0.12em;
  text-transform: uppercase;
  font-weight: 500;
`;

const HeroTitle = styled.h1`
  font-size: 48px;
  font-weight: 700;
  line-height: 1.1;
  color: #fff;
  letter-spacing: -0.02em;
  margin-bottom: 1rem;

  .accent {
    background: linear-gradient(135deg, rgb(var(--color-primary)), rgb(var(--color-secondary)));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  @media (max-width: 900px) {
    font-size: 36px;
  }
`;

const HeroSub = styled.p`
  font-size: 14px;
  color: #6b7280;
  line-height: 1.7;
  max-width: 360px;
  margin-bottom: 2.5rem;
`;

const StatsRow = styled.div`
  display: flex;
  gap: 1.5rem;
  align-items: center;
`;

const Stat = styled.div`
  display: flex;
  flex-direction: column;
  gap: 2px;
`;

const StatNum = styled.div`
  font-size: 20px;
  font-weight: 700;
  color: #fff;
`;

const StatLabel = styled.div`
  font-size: 11px;
  color: #6b7280;
  letter-spacing: 0.08em;
  text-transform: uppercase;
`;

const StatDivider = styled.div`
  width: 0.5px;
  background: #2a2a3a;
  align-self: stretch;
`;

const RightPanel = styled.div`
  width: 420px;
  min-height: 100vh;
  background: rgb(10 10 20 / 0.97);
  border-left: 0.5px solid rgb(255 255 255 / 0.06);
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 2.5rem 2rem;
  position: relative;
  z-index: 2;

  @media (max-width: 900px) {
    width: 100%;
    min-height: auto;
    border-left: none;
    border-top: 0.5px solid rgb(255 255 255 / 0.06);
    padding: 2.5rem 1.5rem;
  }
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

  return (
    <PageWrapper>
      <Canvas ref={canvasRef} />

      <LeftPanel>
        <Badge>
          <BadgeDot />
          <BadgeText>Royal Panel</BadgeText>
        </Badge>
        <HeroTitle>
          Deploy.<br />
          Manage.<br />
          <span className="accent">Dominate.</span>
        </HeroTitle>
        <HeroSub>
          High-performance game server hosting. Spin up servers in seconds, monitor everything in real time.
        </HeroSub>
        <StatsRow>
          <Stat>
            <StatNum>99.9%</StatNum>
            <StatLabel>Uptime</StatLabel>
          </Stat>
          <StatDivider />
          <Stat>
            <StatNum>&lt;2ms</StatNum>
            <StatLabel>Latency</StatLabel>
          </Stat>
          <StatDivider />
          <Stat>
            <StatNum>24/7</StatNum>
            <StatLabel>Support</StatLabel>
          </Stat>
        </StatsRow>
      </LeftPanel>

      <RightPanel>
        <TopBar />
        <FlashMessageRender css={tw`mb-4`} />
        <FormEyebrow>Welcome back</FormEyebrow>
        <FormTitle>Sign in</FormTitle>
        <FormSub>Access your control panel</FormSub>
        <Form {...props} ref={ref}>
          {props.children}
        </Form>
        <Footer />
      </RightPanel>
    </PageWrapper>
  );
});
