import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import ClientDetails from '../ClientDetails';

// Mock the stores
vi.mock('../../stores/inViewStore', () => ({
  useInViewStore: vi.fn()
}));

vi.mock('../../stores/clientsStore', () => ({
  useClientsStore: vi.fn()
}));

vi.mock('../../stores/casesStore', () => ({
  useCasesStore: vi.fn()
}));

// Mock react-toastify
vi.mock('react-toastify', () => ({
  toast: {
    error: vi.fn(),
    success: vi.fn()
  }
}));

describe('ClientDetails Component', () => {
  const mockNavigate = vi.fn();
  const mockUpdateUser = vi.fn();
  const mockGetCases = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
    
    // Mock the stores
    const { useInViewStore } = await vi.importActual('../../stores/inViewStore');
    const { useClientsStore } = await vi.importActual('../../stores/clientsStore');
    const { useCasesStore } = await vi.importActual('../../stores/casesStore');
    
    vi.mocked(useInViewStore).mockImplementation(() => ({
      view: 'clients',
      userId: '123',
      caseId: '',
      name: 'Test Client',
      navigate: mockNavigate
    }));
    
    vi.mocked(useClientsStore).mockImplementation(() => ({
      users: [
        {
          id: '123',
          name: 'Test Client',
          email: 'test@example.com',
          phone: '123-456-7890',
          cellphone: '098-765-4321',
          role: 'Customer',
          created_at: '2023-01-01T00:00:00Z'
        }
      ],
      updateUser: mockUpdateUser
    }));
    
    vi.mocked(useCasesStore).mockImplementation(() => ({
      cases: [
        {
          id: '1',
          id_user: '123',
          title: 'Test Case 1',
          status: 'open',
          created_at: '2023-01-02T00:00:00Z'
        }
      ],
      loadingCases: false,
      page: 1,
      totalPages: 1,
      total: 1,
      searchQuery: '',
      getCases: mockGetCases
    }));
  });

  it('does not render when route is not clients', async () => {
    const { useInViewStore } = await vi.importActual('../../stores/inViewStore');
    vi.mocked(useInViewStore).mockImplementation(() => ({
      view: 'other',
      userId: '123',
      caseId: '',
      name: 'Test Client',
      navigate: mockNavigate
    }));
    
    render(<ClientDetails />);
    expect(screen.queryByText('Test Client')).not.toBeInTheDocument();
  });

  it('does not render when no userId', async () => {
    const { useInViewStore } = await vi.importActual('../../stores/inViewStore');
    vi.mocked(useInViewStore).mockImplementation(() => ({
      view: 'clients',
      userId: '',
      caseId: '',
      name: 'Test Client',
      navigate: mockNavigate
    }));
    
    render(<ClientDetails />);
    expect(screen.queryByText('Test Client')).not.toBeInTheDocument();
  });

  it('calls getCases on mount with correct parameters', () => {
    render(<ClientDetails />);
    expect(mockGetCases).toHaveBeenCalledWith('123', false, 1);
  });

  it('renders client information correctly', () => {
    render(<ClientDetails />);
    
    expect(screen.getByText('Test Client')).toBeInTheDocument();
    expect(screen.getByText('Customer')).toBeInTheDocument();
    expect(screen.getByText('test@example.com')).toBeInTheDocument();
    expect(screen.getByText('123-456-7890')).toBeInTheDocument();
    expect(screen.getByText('098-765-4321')).toBeInTheDocument();
  });

  it('renders cases when store has case items', () => {
    render(<ClientDetails />);
    
    expect(screen.getByText('Test Case 1')).toBeInTheDocument();
  });
});
