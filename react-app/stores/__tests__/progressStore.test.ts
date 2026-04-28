import { describe, it, expect, beforeEach, vi } from 'vitest';

const { mockGet, mockPost, mockPut, mockDel, mockPostMultipart, mockToastSuccess } = vi.hoisted(() => ({
  mockGet: vi.fn(),
  mockPost: vi.fn(),
  mockPut: vi.fn(),
  mockDel: vi.fn(),
  mockPostMultipart: vi.fn(),
  mockToastSuccess: vi.fn(),
}));

vi.mock('../../utils/fetch', () => ({
  get: mockGet,
  post: mockPost,
  put: mockPut,
  del: mockDel,
  postMultipart: mockPostMultipart,
}));

vi.mock('react-toastify', () => ({
  toast: {
    success: mockToastSuccess,
  },
}));

describe('progressStore', () => {
  beforeEach(() => {
    vi.resetModules();
    vi.clearAllMocks();
    vi.stubGlobal('alert', vi.fn());
    (globalThis as any).stolmcData = {
      ...(globalThis as any).stolmcData,
      root_url: 'http://localhost',
      api_url: 'service-tracker-stolmc/v1',
      nonce: 'nonce',
      toast_status_added: 'Status added',
      toast_status_deleted: 'Status deleted',
      toast_status_edited: 'Status edited',
      alert_error_base: 'Error: ',
      alert_blank_status_title: 'Blank status',
    };
  });

  it('getStatus updates state when onlyFetch is false', async () => {
    mockGet.mockResolvedValue({
      data: { success: true, data: [{ id: 1, text: 'hello' }], error_code: null, message: null, meta: {} },
    });
    const { useProgressStore } = await import('../progressStore');

    await useProgressStore.getState().getStatus('case-1', false, 'Case title');

    expect(useProgressStore.getState()).toMatchObject({
      status: [{ id: 1, text: 'hello' }],
      caseTitle: 'Case title',
      loadingStatus: false,
    });
  });

  it('postStatus refetches full list and stores server result', async () => {
    mockPost.mockResolvedValue({ data: {} });
    mockGet.mockResolvedValue({
      data: { success: true, data: [{ id: 10, text: 'new' }], error_code: null, message: null, meta: {} },
    });

    const { useProgressStore } = await import('../progressStore');
    useProgressStore.setState({ caseTitle: 'Title', status: [] });

    await useProgressStore.getState().postStatus('u1', 'c1', 'new text');

    expect(useProgressStore.getState().status).toEqual([{ id: 10, text: 'new' }]);
    expect(mockToastSuccess).toHaveBeenCalledWith('Status added');
  });

  it('editStatus validates blank text', async () => {
    const { useProgressStore } = await import('../progressStore');

    await useProgressStore.getState().editStatus(1, 'u1', '');

    expect(global.alert).toHaveBeenCalled();
    expect(mockPut).not.toHaveBeenCalled();
  });

  it('deleteStatus removes item from local state', async () => {
    mockDel.mockResolvedValue({ data: {} });
    const { useProgressStore } = await import('../progressStore');

    useProgressStore.setState({
      status: [
        { id: 1, text: 'a', id_user: 'u1', _id_case: 'c', created_at: '' } as any,
        { id: 2, text: 'b', id_user: 'u1', _id_case: 'c', created_at: '' } as any,
      ],
    });

    await useProgressStore.getState().deleteStatus(1, '');

    expect(useProgressStore.getState().status).toHaveLength(1);
    expect(useProgressStore.getState().status[0].id).toBe(2);
    expect(mockToastSuccess).toHaveBeenCalledWith('Status deleted');
  });

  it('uploadFiles returns uploaded files on success', async () => {
    mockPostMultipart.mockResolvedValue({
      data: {
        success: true,
        data: { files: [{ url: '/f.png' }] },
        error_code: null,
        message: null,
        meta: {},
      },
    });
    const { useProgressStore } = await import('../progressStore');

    const files = [new File(['x'], 'x.txt', { type: 'text/plain' })];
    const uploaded = await useProgressStore.getState().uploadFiles('u1', 'c1', files);

    expect(uploaded).toEqual([{ url: '/f.png' }]);
  });
});
