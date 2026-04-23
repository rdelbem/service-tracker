import { describe, it, expect, beforeEach, vi } from 'vitest';

const { mockGet, mockPost, mockPut, mockDel, mockToastSuccess, mockToastError } = vi.hoisted(() => ({
  mockGet: vi.fn(),
  mockPost: vi.fn(),
  mockPut: vi.fn(),
  mockDel: vi.fn(),
  mockToastSuccess: vi.fn(),
  mockToastError: vi.fn(),
}));

vi.mock('../../utils/fetch', () => ({
  get: mockGet,
  post: mockPost,
  put: mockPut,
  del: mockDel,
}));

vi.mock('react-toastify', () => ({
  toast: {
    success: mockToastSuccess,
    error: mockToastError,
  },
}));

describe('casesStore', () => {
  beforeEach(() => {
    vi.resetModules();
    vi.clearAllMocks();
    vi.stubGlobal('alert', vi.fn());
    (globalThis as any).data = {
      ...(globalThis as any).data,
      root_url: 'http://localhost',
      api_url: 'service-tracker-stolmc/v1',
      nonce: 'nonce',
      toast_case_added: 'Case added',
      toast_case_deleted: 'Case deleted',
      toast_case_edited: 'Case edited',
      alert_error_base: 'Error: ',
      alert_blank_case_title: 'Blank title',
    };
  });

  it('getCases updates pagination and case list', async () => {
    mockGet.mockResolvedValue({
      data: {
        success: true,
        data: [{ id: 1, title: 'A' }],
        error_code: null,
        message: null,
        meta: { pagination: { page: 2, per_page: 6, total: 11, total_pages: 2 } },
      },
    });

    const { useCasesStore } = await import('../casesStore');

    await useCasesStore.getState().getCases('user-1', false, 2);

    expect(useCasesStore.getState()).toMatchObject({
      user: 'user-1',
      cases: [{ id: 1, title: 'A' }],
      page: 2,
      total: 11,
      totalPages: 2,
      loadingCases: false,
    });
  });

  it('searchCases with empty query restores normal list', async () => {
    mockGet.mockResolvedValue({
      data: {
        success: true,
        data: [{ id: 2, title: 'Restored' }],
        error_code: null,
        message: null,
        meta: { pagination: { page: 1, per_page: 6, total: 1, total_pages: 1 } },
      },
    });

    const { useCasesStore } = await import('../casesStore');

    useCasesStore.setState({ user: 'u1', page: 3, searchQuery: 'abc', cases: [] });
    await useCasesStore.getState().searchCases('', 'u1');

    expect(mockGet).toHaveBeenCalled();
    expect(useCasesStore.getState().cases[0].title).toBe('Restored');
    expect(useCasesStore.getState().searchQuery).toBe('');
  });

  it('setPage clamps page and paginates search results when searching', async () => {
    mockGet.mockResolvedValue({
      data: {
        success: true,
        data: [{ id: 9, title: 'Search result' }],
        error_code: null,
        message: null,
        meta: { pagination: { page: 3, per_page: 6, total: 20, total_pages: 4 } },
      },
    });

    const { useCasesStore } = await import('../casesStore');

    useCasesStore.setState({ totalPages: 4, searchQuery: 'term', perPage: 6, page: 1, loadingCases: false });
    await useCasesStore.getState().setPage('user-1', 99);

    expect(useCasesStore.getState().page).toBe(3);
    expect(useCasesStore.getState().cases[0].title).toBe('Search result');
    expect(String(mockGet.mock.calls[0][0])).toContain('page=4');
  });

  it('toggleCase updates local case status and shows toast', async () => {
    mockPost.mockResolvedValue({ data: {} });
    const { useCasesStore } = await import('../casesStore');

    useCasesStore.setState({
      cases: [{ id: 1, title: 'T', status: 'open', created_at: '', id_user: 'u1' } as any],
    });

    await useCasesStore.getState().toggleCase(1);

    expect(useCasesStore.getState().cases[0].status).toBe('close');
    expect(mockToastSuccess).toHaveBeenCalledWith('Case is now closed');
  });

  it('deleteCase removes case from store', async () => {
    mockDel.mockResolvedValue({ data: {} });
    const { useCasesStore } = await import('../casesStore');

    useCasesStore.setState({
      cases: [
        { id: 1, title: 'A', status: 'open', created_at: '', id_user: 'u1' } as any,
        { id: 2, title: 'B', status: 'open', created_at: '', id_user: 'u1' } as any,
      ],
    });

    await useCasesStore.getState().deleteCase(1, 'A');

    expect(useCasesStore.getState().cases).toHaveLength(1);
    expect(useCasesStore.getState().cases[0].id).toBe(2);
    expect(mockToastSuccess).toHaveBeenCalledWith('Case deleted');
  });

  it('editCase validates blank title and alerts', async () => {
    const { useCasesStore } = await import('../casesStore');

    await useCasesStore.getState().editCase(1, 'u1', '');

    expect(global.alert).toHaveBeenCalled();
    expect(mockPut).not.toHaveBeenCalled();
  });
});
