#!/usr/bin/env python3
"""Render photorealistic volleyball PNGs (sphere shading + drawn seams + shadow)."""
import os
import numpy as np
from PIL import Image, ImageDraw, ImageFilter

LIGHT = np.array([-0.42, -0.56, 0.72])
LIGHT /= np.linalg.norm(LIGHT)


def shaded_sphere(size, R, cx, cy, base_color):
    W = H = size
    y, x = np.mgrid[0:H, 0:W].astype(np.float64)
    dx = x - cx; dy = y - cy
    d2 = dx*dx + dy*dy
    inside = d2 < R*R
    z = np.where(inside, np.sqrt(np.maximum(R*R - d2, 0.0)), 0.0)
    nx, ny, nz = dx/R, dy/R, z/R
    diff = np.clip(nx*LIGHT[0] + ny*LIGHT[1] + nz*LIGHT[2], 0, 1)
    rz = 2*diff*nz - LIGHT[2]
    spec = np.clip(rz, 0, 1) ** 32
    edge = np.where(inside, np.clip(nz, 0, 1) ** 0.4, 1.0)
    base = np.array(base_color, dtype=np.float64) / 255.0
    shaded = base[None,None,:] * (0.44 + 0.54 * diff[:,:,None])
    shaded += spec[:,:,None] * 0.55
    shaded *= (0.55 + 0.45 * edge[:,:,None])
    rng = np.random.default_rng(7)
    noise = (rng.random((H, W, 3)) - 0.5) * 0.022
    shaded = np.clip(shaded + noise, 0, 1)
    rgba = np.zeros((H, W, 4), dtype=np.uint8)
    rgba[..., :3] = (shaded * 255).astype(np.uint8)
    aa = np.clip((R - np.sqrt(d2)) / 1.5, 0, 1)
    rgba[..., 3] = (aa * 255).astype(np.uint8)
    return Image.fromarray(rgba, 'RGBA')


def seam_polylines(R, cx, cy, samples=240):
    """Three great circles arranged as an asterisk through the ball's
    front-center point: vertical, horizontal, and a diagonal. Each is the
    intersection of a plane (whose normal lies in the XY plane) with the
    sphere — so each arc passes through screen-center on the front
    hemisphere and reaches the silhouette at two opposite points."""
    normals = [
        (1.0, 0.0, 0.0),                # vertical seam through center
        (0.0, 1.0, 0.0),                # horizontal seam through center
        (0.70711, 0.70711, 0.0),        # diagonal upper-right to lower-left
    ]
    polylines = []
    for sn_tuple in normals:
        sn = np.array(sn_tuple, dtype=np.float64)
        sn /= np.linalg.norm(sn)
        # v1 perpendicular to sn (use cross with z if sn isn't aligned with z)
        if abs(sn[2]) < 0.9:
            v1 = np.cross(sn, [0.0, 0.0, 1.0])
        else:
            v1 = np.cross(sn, [1.0, 0.0, 0.0])
        v1 = v1 / np.linalg.norm(v1)
        v2 = np.cross(sn, v1)
        v2 = v2 / np.linalg.norm(v2)

        pts = []
        for t in np.linspace(0, 2*np.pi, samples):
            p = np.cos(t)*v1 + np.sin(t)*v2
            if p[2] >= 0:           # front hemisphere only
                sx = cx + p[0] * R
                sy = cy + p[1] * R
                pts.append((sx, sy))
        if len(pts) >= 2:
            polylines.append(pts)
    return polylines


def draw_seams(ball_img, R, cx, cy, seam_color, light_dir=LIGHT):
    """Draw seam curves on the ball image with a subtle 3D groove effect."""
    W, H = ball_img.size
    overlay = Image.new('RGBA', (W, H), (0,0,0,0))
    od = ImageDraw.Draw(overlay)
    base_w = max(3, int(R * 0.028))
    # Highlight offset (toward light) and shadow offset (away)
    ox = -int(R * 0.012 * light_dir[0])   # screen offset in pixels
    oy = -int(R * 0.012 * light_dir[1])
    # Tip: lighter color = lightened base; we'll use seam_color blended toward white
    seam_rgb = np.array(seam_color)
    # 2 stroke passes: a slight bright highlight beside the seam, then the dark seam itself
    light_edge = tuple(np.clip((seam_rgb * 0.7 + np.array([255,255,255]) * 0.3), 0, 255).astype(int)) + (140,)
    dark_seam = tuple(seam_color) + (255,)
    polylines = seam_polylines(R, cx, cy)
    for pl in polylines:
        if len(pl) < 2:
            continue
        # Pass 1: lighter offset stroke (highlight side of groove)
        shifted_light = [(x - ox*0.5, y - oy*0.5) for (x, y) in pl]
        od.line(shifted_light, fill=light_edge, width=max(2, base_w-1), joint='curve')
    overlay = overlay.filter(ImageFilter.GaussianBlur(radius=0.6))
    # Pass 2: the seam itself, sharp
    od2 = ImageDraw.Draw(overlay)
    for pl in polylines:
        if len(pl) < 2:
            continue
        od2.line(pl, fill=dark_seam, width=base_w, joint='curve')
    # Mask the overlay so it only shows inside the ball
    # Make a circle mask
    mask = Image.new('L', (W, H), 0)
    md = ImageDraw.Draw(mask)
    md.ellipse([cx-R+1, cy-R+1, cx+R-1, cy+R-1], fill=255)
    overlay.putalpha(Image.eval(
        Image.merge('L', [overlay.split()[3]]),
        lambda v: v
    ))
    # simpler: alpha_composite then mask via paste with mask
    composed = ball_img.copy()
    composed.alpha_composite(overlay)
    # Re-apply original alpha so we don't extend past silhouette
    final = Image.composite(composed, ball_img, mask)
    return final


def render(out_path, base=(250,248,240), seam=(20,32,42), size=900, ball_ratio=0.42, shadow=True):
    W = H = size
    cx = cy = W / 2.0
    R = size * ball_ratio

    ball = shaded_sphere(size, R, cx, cy, base)
    ball = draw_seams(ball, R, cx, cy, seam)

    if shadow:
        extra = int(R * 0.35)
        canvas = Image.new('RGBA', (W, H + extra), (0,0,0,0))
        sh = Image.new('RGBA', canvas.size, (0,0,0,0))
        sd = ImageDraw.Draw(sh)
        sx = cx - R*0.78
        sy = cy + R*0.88
        sd.ellipse([sx, sy, cx + R*0.78, sy + R*0.32], fill=(8,15,25,115))
        sh = sh.filter(ImageFilter.GaussianBlur(radius=22))
        canvas.alpha_composite(sh)
        canvas.alpha_composite(ball)
        canvas.save(out_path, optimize=True)
    else:
        ball.save(out_path, optimize=True)
    print("wrote", out_path, os.path.getsize(out_path), "bytes")


def main():
    v3 = "/home/user/Test_Run/atxbeach_site_v3/images/photos"
    v4 = "/home/user/Test_Run/atxbeach_site_v4/images/photos"
    os.makedirs(v3, exist_ok=True); os.makedirs(v4, exist_ok=True)

    render(f"{v3}/volleyball-white.png", base=(252,250,243), seam=(20,32,42))
    # UNO-style colored balls for the v3 Wild Card landing
    render(f"{v3}/volleyball-blue.png",   base=(60,130,215),  seam=(255,253,245))
    render(f"{v3}/volleyball-yellow.png", base=(240,213,48),  seam=(20,32,42))
    render(f"{v3}/volleyball-green.png",  base=(50,180,80),   seam=(255,253,245))
    render(f"{v3}/volleyball-red.png",    base=(215,50,50),   seam=(255,253,245))
    render(f"{v4}/volleyball-teal.png",  base=(26,166,183),  seam=(255,253,245))
    render(f"{v4}/volleyball-coral.png", base=(255,107,74),  seam=(255,253,245))
    render(f"{v4}/volleyball-sun.png",   base=(255,193,77),  seam=(20,32,42))
    render(f"{v4}/volleyball-plum.png",  base=(90,79,207),   seam=(255,253,245))


if __name__ == "__main__":
    main()
