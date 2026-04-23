import { describe, it, expect, beforeEach, vi } from 'vitest';
import { request, get, post, put, del, postMultipart } from '../fetch';

describe('fetch utils', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('sends JSON request bodies with content-type by default', async () => {
    const mockJson = vi.fn().mockResolvedValue({ ok: true });
    vi.mocked(global.fetch).mockResolvedValue({
      ok: true,
      headers: { get: () => 'application/json' },
      json: mockJson,
      text: vi.fn(),
    } as any);

    await post('/api/test', { foo: 'bar' }, { headers: { 'X-Test': '1' } });

    expect(global.fetch).toHaveBeenCalledWith('/api/test', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Test': '1',
      },
      body: JSON.stringify({ foo: 'bar' }),
    });
  });

  it('throws on non-ok responses', async () => {
    vi.mocked(global.fetch).mockResolvedValue({
      ok: false,
      status: 500,
      headers: { get: () => 'application/json' },
      json: vi.fn(),
      text: vi.fn(),
    } as any);

    await expect(request('/api/fail')).rejects.toThrow('HTTP error! status: 500');
  });

  it('parses non-json responses as text', async () => {
    vi.mocked(global.fetch).mockResolvedValue({
      ok: true,
      headers: { get: () => 'text/plain' },
      json: vi.fn(),
      text: vi.fn().mockResolvedValue('plain'),
    } as any);

    const res = await get('/api/text');

    expect(res).toEqual({
      data: {
        success: true,
        data: 'plain',
        error_code: null,
        message: null,
        meta: {},
      },
    });
  });

  it('returns null for empty non-json response body', async () => {
    vi.mocked(global.fetch).mockResolvedValue({
      ok: true,
      headers: { get: () => null },
      json: vi.fn(),
      text: vi.fn().mockResolvedValue(''),
    } as any);

    const res = await del('/api/empty');

    expect(res).toEqual({
      data: {
        success: true,
        data: null,
        error_code: null,
        message: null,
        meta: {},
      },
    });
  });

  it('does not force content-type for multipart uploads', async () => {
    vi.mocked(global.fetch).mockResolvedValue({
      ok: true,
      headers: { get: () => 'application/json' },
      json: vi.fn().mockResolvedValue({ success: true }),
      text: vi.fn(),
    } as any);

    const fd = new FormData();
    fd.append('file', new Blob(['a']), 'a.txt');

    await postMultipart('/api/upload', fd, { headers: { 'X-WP-Nonce': 'abc' } });

    expect(global.fetch).toHaveBeenCalledWith('/api/upload', {
      method: 'POST',
      headers: { 'X-WP-Nonce': 'abc' },
      body: fd,
    });
  });

  it('supports put helper with body', async () => {
    vi.mocked(global.fetch).mockResolvedValue({
      ok: true,
      headers: { get: () => 'application/json' },
      json: vi.fn().mockResolvedValue({ updated: true }),
      text: vi.fn(),
    } as any);

    const res = await put('/api/item/1', { title: 'New' });

    expect(res).toEqual({
      data: {
        success: true,
        data: { updated: true },
        error_code: null,
        message: null,
        meta: {},
      },
    });
  });
});
